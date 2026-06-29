from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import insightface
from insightface.app import FaceAnalysis
import numpy as np
import faiss
import json, os, io, threading, traceback, time
from PIL import Image

app = FastAPI(root_path="/api-face")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ─────────────────────────────────────────
#  Config
# ─────────────────────────────────────────
ENCODINGS_DIR = "encodings"
FAISS_PATH    = "encodings.faiss"
IDMAP_PATH    = "id_map.json"
EMBEDDING_DIM = 512
THRESHOLD     = 0.38

os.makedirs(ENCODINGS_DIR, exist_ok=True)

# ─────────────────────────────────────────
#  InsightFace — buffalo_sc (ringan & cepat)
#  det_size dinaikkan ke 480x480 supaya wajah kecil
#  (saat banyak orang dalam 1 frame) tetap terdeteksi.
# ─────────────────────────────────────────
face_app = FaceAnalysis(
    name      = "buffalo_sc",
    providers = ["CPUExecutionProvider"]
)
face_app.prepare(ctx_id=0, det_size=(480, 480))

# ─────────────────────────────────────────
#  FAISS index (global, protected by lock)
# ─────────────────────────────────────────
lock        = threading.Lock()
faiss_index = faiss.IndexFlatIP(EMBEDDING_DIM)
id_map      = []


# ─────────────────────────────────────────
#  Helper functions
# ─────────────────────────────────────────

def normalize(v: np.ndarray) -> np.ndarray:
    """L2-normalize vector supaya IndexFlatIP = cosine similarity."""
    norm = np.linalg.norm(v)
    return (v / norm).astype(np.float32) if norm > 0 else v.astype(np.float32)


def read_image(file: UploadFile) -> np.ndarray:
    """Baca UploadFile → numpy array RGB dengan penanganan error aman."""
    try:
        contents = file.file.read()
        if not contents:
            raise ValueError("File kosong atau stream biner rusak.")

        file.file.seek(0)

        img = Image.open(io.BytesIO(contents)).convert("RGB")
        return np.array(img)
    except Exception as e:
        print(f"[ERROR BACA GAMBAR] {str(e)}")
        raise ValueError(f"Gagal memproses file {file.filename} menjadi gambar valid. Error: {str(e)}")


def get_faces(img_array: np.ndarray) -> list:
    """Jalankan InsightFace pada gambar. Return list of (embedding, bbox, det_score),
    diurutkan dari det_score tertinggi ke terendah."""
    faces = face_app.get(img_array)
    if not faces:
        return []
    results = [(normalize(f.embedding), f.bbox, f.det_score) for f in faces]
    results.sort(key=lambda x: x[2], reverse=True)
    return results


def rebuild_index():
    """Rebuild FAISS index dari semua file JSON di folder encodings/."""
    global faiss_index, id_map

    new_index = faiss.IndexFlatIP(EMBEDDING_DIM)
    new_map   = []

    for fname in sorted(os.listdir(ENCODINGS_DIR)):
        if not fname.endswith(".json"):
            continue
        try:
            uid = int(fname.replace(".json", ""))
            with open(f"{ENCODINGS_DIR}/{fname}") as f:
                vec = np.array(json.load(f), dtype=np.float32)
            new_index.add(np.array([vec]))
            new_map.append(uid)
        except Exception as e:
            print(f"[REBUILD] Skip {fname}: {e}")

    faiss_index = new_index
    id_map      = new_map

    try:
        faiss.write_index(faiss_index, FAISS_PATH)
        with open(IDMAP_PATH, "w") as f:
            json.dump(id_map, f)
        print(f"[INDEX] Rebuilt: {faiss_index.ntotal} entries")
    except Exception as e:
        print(f"[CRITICAL PERMISSION ERROR] Tidak bisa menulis data index ke disk: {e}")


# ─────────────────────────────────────────
#  Startup
# ─────────────────────────────────────────

@app.on_event("startup")
async def startup():
    rebuild_index()
    print("[WARMUP] Warming up InsightFace...")
    dummy = np.zeros((480, 480, 3), dtype=np.uint8)
    face_app.get(dummy)
    print("[WARMUP] Done! Server siap.")


# ─────────────────────────────────────────
#  REGISTER — POST /register/{user_id}
# ─────────────────────────────────────────

@app.post("/register/{user_id}")
async def register_face(user_id: int, photos: list[UploadFile] = File(...)):
    """
    Terima beberapa foto wajah, simpan rata-rata embedding.
    Aman dari crash internal server (Error 500 ditangkap & dilaporkan).
    """
    try:
        embeddings = []
        failed     = []

        print(f"[REGISTER] Memproses pendaftaran untuk User ID: {user_id}. Jumlah foto: {len(photos)}")

        for i, photo in enumerate(photos):
            try:
                img   = read_image(photo)
                faces = get_faces(img)

                if faces:
                    best_embedding = faces[0][0]
                    embeddings.append(best_embedding)
                    print(f"[REGISTER] Foto {i+1} ({photo.filename}): Wajah terdeteksi, det_score={faces[0][2]:.3f}")
                else:
                    failed.append(photo.filename if photo.filename else f"Foto_{i+1}")
                    print(f"[REGISTER] Foto {i+1} ({photo.filename}): Wajah TIDAK terdeteksi")
            except Exception as img_err:
                print(f"[REGISTER ERROR] Gagal membaca indeks foto ke-{i}: {str(img_err)}")
                failed.append(photo.filename if photo.filename else f"Foto_{i+1} (Error)")

        if len(embeddings) == 0:
            raise HTTPException(
                status_code = 422,
                detail      = f"Gagal mendaftar. Dari {len(photos)} foto yang dikirim, tidak ada wajah yang terdeteksi secara jelas. Pastikan pencahayaan cukup."
            )

        avg_embedding = normalize(np.mean(embeddings, axis=0))

        json_path = f"{ENCODINGS_DIR}/{user_id}.json"

        try:
            with open(json_path, "w") as f:
                json.dump(avg_embedding.tolist(), f)
        except IOError as io_err:
            raise HTTPException(
                status_code = 500,
                detail      = f"Gagal menulis file wajah di server AI. Periksa izin akses folder. Detail: {str(io_err)}"
            )

        with lock:
            rebuild_index()

        return {
            "status"    : "ok",
            "user_id"   : user_id,
            "registered": len(embeddings),
            "failed"    : failed,
            "message"   : f"Wajah berhasil didaftarkan dari {len(embeddings)} foto."
        }

    except HTTPException as http_err:
        raise http_err
    except Exception as e:
        error_lines = traceback.format_exc()
        print(f"[CRASH REPORT SYSTEM]:\n{error_lines}")
        raise HTTPException(
            status_code = 500,
            detail      = f"Sistem Python Crash: {str(e)}. Terjadi pada bagian: {error_lines.splitlines()[-2]}"
        )


# ─────────────────────────────────────────
#  IDENTIFY — POST /identify
#  Mendeteksi & mengidentifikasi SEMUA wajah dalam 1 frame sekaligus.
# ─────────────────────────────────────────

@app.post("/identify")
async def identify_face(photo: UploadFile = File(...)):
    """Terima 1 foto, identifikasi semua wajah yang terdeteksi di dalamnya."""
    t0 = time.time()
    try:
        img   = read_image(photo)
        faces = get_faces(img)

        if not faces:
            return {
                "found"               : False,
                "count"               : 0,
                "total_faces_detected": 0,
                "matches"             : [],
                "reason"              : "Wajah tidak terdeteksi di foto",
            }

        with lock:
            if faiss_index.ntotal == 0:
                return {
                    "found"               : False,
                    "count"               : 0,
                    "total_faces_detected": len(faces),
                    "matches"             : [],
                    "reason"              : "Belum ada wajah terdaftar di database",
                }

            # Batch search: semua wajah di frame dicari sekaligus ke FAISS
            query = np.array([f[0] for f in faces], dtype=np.float32)
            scores, indices = faiss_index.search(query, k=1)

        matches   = []
        seen_uids = set()  # cegah 1 user_id dobel kalau 2 deteksi nyangkut ke ID sama

        for i, (emb, bbox, det_score) in enumerate(faces):
            score = float(scores[i][0])
            idx   = int(indices[i][0])

            if idx >= 0 and score >= THRESHOLD:
                uid = id_map[idx]
                if uid in seen_uids:
                    continue
                seen_uids.add(uid)
                matches.append({
                    "user_id"   : uid,
                    "confidence": round(score * 100, 1),
                    "distance"  : round(1 - score, 4),
                    "bbox"      : [float(x) for x in bbox],
                })

        elapsed_ms = round((time.time() - t0) * 1000, 1)
        print(f"[IDENTIFY] {len(faces)} wajah terdeteksi, {len(matches)} cocok ({elapsed_ms}ms): {[m['user_id'] for m in matches]}")

        return {
            "found"               : len(matches) > 0,
            "count"               : len(matches),
            "total_faces_detected": len(faces),
            "matches"             : matches,
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Gagal mengidentifikasi wajah: {str(e)}")


# ─────────────────────────────────────────
#  CHECK FACE — POST /check-face
# ─────────────────────────────────────────

@app.post("/check-face")
async def check_face(photo: UploadFile = File(...)):
    """Cek apakah wajah terdeteksi di foto — TANPA compare ke database."""
    try:
        img   = read_image(photo)
        faces = get_faces(img)

        if not faces:
            return {
                "detected"     : False,
                "face_count"   : 0,
                "quality_score": None,
                "reason"       : "Tidak ada wajah terdeteksi. Pastikan wajah menghadap kamera dan cahaya cukup.",
            }

        best_score = float(faces[0][2])

        return {
            "detected"     : True,
            "face_count"   : len(faces),
            "quality_score": round(best_score, 3),
            "reason"       : None,
        }
    except Exception as e:
        return {"detected": False, "face_count": 0, "quality_score": None, "reason": f"File Rusak: {str(e)}"}


# ─────────────────────────────────────────
#  DELETE — DELETE /register/{user_id}
# ─────────────────────────────────────────

@app.delete("/register/{user_id}")
async def delete_face(user_id: int):
    """Hapus data wajah user tertentu."""
    json_path = f"{ENCODINGS_DIR}/{user_id}.json"

    if not os.path.exists(json_path):
        raise HTTPException(status_code=404, detail="User tidak ditemukan")

    os.remove(json_path)

    with lock:
        rebuild_index()

    return {"status": "ok", "message": f"Data wajah user {user_id} dihapus"}


# ─────────────────────────────────────────
#  RELOAD CACHE — POST /reload-cache
# ─────────────────────────────────────────

@app.post("/reload-cache")
async def reload_cache():
    """Force rebuild index dari disk."""
    with lock:
        rebuild_index()
    return {"status": "ok", "total": faiss_index.ntotal}


# ─────────────────────────────────────────
#  HEALTH CHECK — GET /health
# ─────────────────────────────────────────

@app.get("/health")
def health():
    return {
        "status"   : "ok",
        "model"    : "InsightFace buffalo_sc",
        "indexed"  : faiss_index.ntotal,
        "threshold": THRESHOLD,
    }