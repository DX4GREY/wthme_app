# 1. Gunakan Python 3.10-slim tapi tambahkan build tools
FROM python:3.10-slim

# 2. Install dependencies sistem (WAJIB untuk OpenCV & Compiling InsightFace)
RUN apt-get update && apt-get install -y \
    build-essential \
    cmake \
    g++ \
    libgl1 \
    libglx-mesa0 \
    libglib2.0-0 \
    libsm6 \
    libxext6 \
    libxrender1 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# 3. Upgrade pip & setuptools sebelum install requirements
RUN pip install --no-cache-dir --upgrade pip setuptools wheel

# 4. Copy & Install Requirements
COPY requirements.txt .

# Install numpy versi stabil untuk InsightFace agar tidak konflik
RUN pip install --no-cache-dir numpy==1.26.4
RUN pip install --no-cache-dir -r requirements.txt

# 5. Copy file project
COPY main.py .
RUN mkdir -p encodings

# 6. Pre-download Model (Warmup)
# Pastikan koneksi internet stabil saat build, karena ini akan download model +- 300MB
RUN python -c "from insightface.app import FaceAnalysis; app = FaceAnalysis(name='buffalo_sc', providers=['CPUExecutionProvider']); app.prepare(ctx_id=0, det_size=(320,320))"

EXPOSE 8001

# 7. Jalankan Uvicorn
# CATATAN: Gunakan --workers 1 jika RAM VPS kamu hanya 2GB. 
# InsightFace sangat rakus RAM, 2 workers bisa memakan > 1.5GB RAM.
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "1"]