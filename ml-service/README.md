# Smart Waste Machine Learning & AI Detection Service

Layanan microservice Machine Learning berbasis **FastAPI** dan **TensorFlow/Keras** untuk mengklasifikasikan citra sampah secara otomatis ke dalam 3 kategori utama: **Organic Waste**, **Plastic Waste**, dan **Paper Waste**.

---

## 1. Ringkasan & Spesifikasi Model

- **Arsitektur Model**: MobileNetV2 (*Transfer Learning* dengan bobot pre-trained ImageNet)
- **Framework**: Python 3.10, TensorFlow 2.x, Keras 3
- **Input Shape**: 224 × 224 × 3 (RGB)
- **Jumlah Kelas**: 3 Kelas
  1. `Organic Waste` (Kategori: Organik - sisa makanan, kulit buah, sayuran, dedaunan)
  2. `Paper Waste` (Kategori: Anorganik - kertas, kardus, koran, karton)
  3. `Plastic Waste` (Kategori: Anorganik - botol plastik, gelas kemasan, kantong plastik)
- **Target Akurasi Pengujian**: Minimal 80.00%
- **Akurasi Pengujian yang Dicapai**: **95.56%** (Melampaui target)

---

## 2. Sumber Dataset & Distribusi Data

Dataset dikurasi dan distandarisasi dari dua sumber repositori data terbuka terpercaya:
1. **TrashNet Dataset** (Gary Thung & Mindy Yang, Stanford University CS229)
   - Sumber: [garythung/trashnet](https://github.com/garythung/trashnet)
   - Penggunaan: Kategori `Plastic Waste` dan `Paper Waste` (Cardboard + Paper)
2. **Kaggle Waste Classification Dataset**
   - Sumber: [kurnivan-ny/waste-classification](https://github.com/kurnivan-ny/waste-classification)
   - Penggunaan: Kategori `Organic Waste` (Food waste, fruit peels, vegetable scraps)

### Distribusi Data (Stratified Split 70% / 15% / 15%)

| Kategori Sampah | Training Set (70%) | Validation Set (15%) | Testing Set (15%) | Total Gambar |
| :--- | :---: | :---: | :---: | :---: |
| **Organic Waste** | 70 | 15 | 15 | 100 |
| **Paper Waste** | 70 | 15 | 15 | 100 |
| **Plastic Waste** | 70 | 15 | 15 | 100 |
| **Total** | **210** | **45** | **45** | **300** |

---

## 3. Hasil Evaluasi Model

Pengujian dilakukan secara ketat pada data testing (45 gambar yang sama sekali belum pernah dilihat oleh model saat proses pelatihan).

### Classification Report

```
              precision    recall  f1-score   support

     organic     1.0000    0.9333    0.9655        15
       paper     0.9375    1.0000    0.9677        15
     plastic     0.9333    0.9333    0.9333        15

    accuracy                         0.9556        45
   macro avg     0.9569    0.9556    0.9555        45
weighted avg     0.9569    0.9556    0.9555        45
```

- **Overall Test Accuracy**: **95.56%**
- **Macro Average F1-Score**: **0.9555**

Artefak grafik evaluasi tersimpan di:
- Matriks Konfusi: `reports/confusion_matrix.png`
- Kurva Akurasi & Loss: `reports/training_curves.png`
- Ringkasan Metrik Lengkap: `reports/classification_report.txt` dan `reports/metrics.json`

---

## 4. Panduan Instalasi & Menjalankan Service

### A. Prasyarat
- Python 3.10+
- Pip & Virtual Environment

### B. Setup Virtual Environment
```powershell
cd ml-service
python -m venv .venv
.\.venv\Scripts\activate
pip install -r requirements.txt
```

### C. Menjalankan Uji Otomatis (Testing Suite)
```powershell
pytest tests/ -v
```

### D. Menjalankan REST API Server
```powershell
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```
- Server berjalan di: `http://localhost:8000`
- Dokumentasi Interaktif (Swagger UI): `http://localhost:8000/docs`

---

## 5. Dokumentasi API Endpoint

### 1. `GET /`
Cek status dasar layanan.
- **Response**:
```json
{
  "service": "smart-waste-ml-service",
  "status": "online",
  "version": "1.0.0",
  "docs": "/docs"
}
```

### 2. `GET /health`
Cek kesiapan model ML dan kelas yang didukung.
- **Response**:
```json
{
  "status": "healthy",
  "model_loaded": true,
  "classes": [
    "Organic Waste",
    "Paper Waste",
    "Plastic Waste"
  ]
}
```

### 3. `POST /predict`
Melakukan prediksi klasifikasi citra sampah.
- **Content-Type**: `multipart/form-data`
- **Body Field**: `file` (berkas gambar: `.jpg`, `.jpeg`, `.png`, atau `.webp`)

#### Contoh Permintaan cURL:
```bash
curl -X POST "http://localhost:8000/predict" \
     -H "accept: application/json" \
     -H "Content-Type: multipart/form-data" \
     -F "file=@dataset/test/plastic/plastic_0001.jpg"
```

#### Contoh Respon Berhasil (`200 OK`):
```json
{
  "status": "success",
  "filename": "plastic_0001.jpg",
  "data": {
    "label": "Plastic Waste",
    "category": "Anorganik (Plastik)",
    "confidence": 0.9854,
    "probabilities": {
      "Organic Waste": 0.0041,
      "Paper Waste": 0.0105,
      "Plastic Waste": 0.9854
    }
  }
}
```

#### Contoh Respon Error Format Tidak Didukung (`400 Bad Request`):
```json
{
  "detail": "Uploaded file is not a supported image format. Allowed: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']"
}
```
