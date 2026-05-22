from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import insightface
from insightface.app import FaceAnalysis
import numpy as np
import faiss
import json, os, io, threading
from PIL import Image

app = FastAPI()

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
# Cosine similarity threshold: score >= ini = cocok
# 0.35 = agak longgar, 0.45 = ketat. Mulai dari 0.38 lalu tuning.
THRESHOLD = 0.38

os.makedirs(ENCODINGS_DIR, exist_ok=True)

# ─────────────────────────────────────────
#  InsightFace — buffalo_sc (ringan & cepat)
# ─────────────────────────────────────────
face_app = FaceAnalysis(
    name      = "buffalo_sc",
    providers = ["CPUExecutionProvider"]
)
# det_size 320x320 cukup untuk foto close-up gate
face_app.prepare(ctx_id=0, det_size=(320, 320))

# ─────────────────────────────────────────
#  FAISS index (global, protected by lock)
# ─────────────────────────────────────────
lock        = threading.Lock()
faiss_index = faiss.IndexFlatIP(EMBEDDING_DIM)  # Inner Product = cosine similarity jika L2-normalized
id_map      = []   # id_map[i] = user_id untuk row ke-i di faiss_index


# ─────────────────────────────────────────
#  Helper functions
# ─────────────────────────────────────────

def normalize(v: np.ndarray) -> np.ndarray:
    """L2-normalize vector supaya IndexFlatIP = cosine similarity."""
    norm = np.linalg.norm(v)
    return (v / norm).astype(np.float32) if norm > 0 else v.astype(np.float32)


def read_image(file: UploadFile) -> np.ndarray:
    """Baca UploadFile → numpy array RGB."""
    contents = file.file.read()
    img = Image.open(io.BytesIO(contents)).convert("RGB")
    return np.array(img)


def get_faces(img_array: np.ndarray) -> list:
    """
    Jalankan InsightFace pada gambar.
    Return list of (normalized_embedding, bbox, detection_score).
    Diurutkan dari detection_score tertinggi (wajah paling jelas).
    """
    faces = face_app.get(img_array)
    if not faces:
        return []
    results = [(normalize(f.embedding), f.bbox, f.det_score) for f in faces]
    results.sort(key=lambda x: x[2], reverse=True)
    return results


def rebuild_index():
    """
    Rebuild FAISS index dari semua file JSON di folder encodings/.
    Dipanggil setiap kali ada register/delete.
    """
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

    # Persist ke disk
    faiss.write_index(faiss_index, FAISS_PATH)
    with open(IDMAP_PATH, "w") as f:
        json.dump(id_map, f)

    print(f"[INDEX] Rebuilt: {faiss_index.ntotal} entries")


# ─────────────────────────────────────────
#  Startup
# ─────────────────────────────────────────

@app.on_event("startup")
async def startup():
    # Load index dari disk jika ada
    rebuild_index()

    # Warmup model — supaya request pertama tidak lambat
    print("[WARMUP] Warming up InsightFace...")
    dummy = np.zeros((320, 320, 3), dtype=np.uint8)
    face_app.get(dummy)
    print("[WARMUP] Done! Server siap.")


# ─────────────────────────────────────────
#  REGISTER — POST /register/{user_id}
# ─────────────────────────────────────────

@app.post("/register/{user_id}")
async def register_face(user_id: int, photos: list[UploadFile] = File(...)):
    """
    Terima 3 foto wajah, simpan rata-rata embedding.
    Otomatis overwrite jika user_id sudah pernah daftar.
    """
    embeddings = []
    failed     = []

    for i, photo in enumerate(photos):
        img   = read_image(photo)
        faces = get_faces(img)

        if faces:
            # Ambil wajah dengan detection score tertinggi
            best_embedding = faces[0][0]
            embeddings.append(best_embedding)
            print(f"[REGISTER] Foto {i+1}: wajah terdeteksi, det_score={faces[0][2]:.3f}")
        else:
            failed.append(i + 1)
            print(f"[REGISTER] Foto {i+1}: wajah tidak terdeteksi")

    if len(embeddings) == 0:
        raise HTTPException(
            status_code = 422,
            detail      = "Tidak ada wajah terdeteksi di semua foto. Pastikan pencahayaan cukup dan wajah menghadap kamera."
        )

    # Rata-ratakan semua embedding yang berhasil, lalu normalize ulang
    avg_embedding = normalize(np.mean(embeddings, axis=0))

    # Simpan ke JSON (source of truth)
    json_path = f"{ENCODINGS_DIR}/{user_id}.json"
    with open(json_path, "w") as f:
        json.dump(avg_embedding.tolist(), f)

    # Rebuild FAISS index
    with lock:
        rebuild_index()

    return {
        "status"    : "ok",
        "user_id"   : user_id,
        "registered": len(embeddings),
        "failed"    : failed,
        "message"   : f"Wajah berhasil didaftarkan dari {len(embeddings)}/{len(photos)} foto"
    }


# ─────────────────────────────────────────
#  IDENTIFY — POST /identify
# ─────────────────────────────────────────

@app.post("/identify")
async def identify_face(photo: UploadFile = File(...)):
    """
    Terima 1 foto, cari wajah paling mirip di FAISS index.
    Return user_id + confidence jika cocok.
    """
    img   = read_image(photo)
    faces = get_faces(img)

    if not faces:
        return {"found": False, "reason": "Wajah tidak terdeteksi di foto"}

    # Pakai wajah dengan detection score tertinggi
    query_embedding = faces[0][0]
    query           = np.array([query_embedding])

    with lock:
        if faiss_index.ntotal == 0:
            return {"found": False, "reason": "Belum ada wajah terdaftar"}

        # Search top-1
        scores, indices = faiss_index.search(query, k=1)

    score = float(scores[0][0])
    idx   = int(indices[0][0])

    print(f"[IDENTIFY] score={round(score,4)} | threshold={THRESHOLD} | idx={idx} | user_id={id_map[idx] if idx >= 0 else 'N/A'}")

    # score = cosine similarity (0.0–1.0), makin tinggi makin mirip
    if idx >= 0 and score >= THRESHOLD:
        matched_user_id = id_map[idx]
        return {
            "found"     : True,
            "user_id"   : matched_user_id,
            "confidence": round(score * 100, 1),
            "distance"  : round(1 - score, 4),
        }

    return {
        "found" : False,
        "reason": f"Wajah tidak cocok (score={round(score,4)}, butuh >={THRESHOLD})"
    }

# ─────────────────────────────────────────────────────────────────
#  Tambahkan endpoint ini ke main.py, setelah endpoint /identify
# ─────────────────────────────────────────────────────────────────

@app.post("/check-face")
async def check_face(photo: UploadFile = File(...)):
    """
    Cek apakah wajah terdeteksi di foto — TANPA compare ke database.
    Dipakai oleh halaman pendaftaran peserta untuk validasi sebelum daftar.

    Return:
      detected      : bool   — apakah ada wajah
      face_count    : int    — jumlah wajah yang terdeteksi
      quality_score : float  — skor kualitas 0.0–1.0 (dari det_score InsightFace)
      reason        : str    — pesan jika tidak terdeteksi
    """
    img   = read_image(photo)
    faces = get_faces(img)   # fungsi yang sudah ada di main.py

    if not faces:
        return {
            "detected"     : False,
            "face_count"   : 0,
            "quality_score": None,
            "reason"       : "Tidak ada wajah terdeteksi. Pastikan wajah menghadap kamera dan cahaya cukup.",
        }

    # Ambil det_score wajah terbaik (sudah diurutkan dari tertinggi)
    best_score = float(faces[0][2])   # faces[0] = (embedding, bbox, det_score)

    return {
        "detected"     : True,
        "face_count"   : len(faces),
        "quality_score": round(best_score, 3),
        "reason"       : None,
    }

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
    """Force rebuild index dari disk. Berguna setelah manual edit file."""
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