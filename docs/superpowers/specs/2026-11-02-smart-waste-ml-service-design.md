# Design Spec: Smart Waste Management - Sub-project 1: Machine Learning & FastAPI Service

- **Date:** 2026-11-02
- **Status:** Approved by User
- **Scope:** Sub-project 1 of Smart Waste Management System (ML Model + Evaluation + FastAPI REST API)

---

## 1. Context & Overall System Architecture

The Smart Waste Management System is designed to streamline waste reporting, management, and automated classification across three core components:
1. **Sub-project 1: Machine Learning & FastAPI Service** (`ml-service/`) — Automated image classification for waste types and REST API inference.
2. **Sub-project 2: Backend API & Web Admin** (`backend/`) — Laravel 11 + MySQL, role-based auth (Admin, User), report CRUD, status updates, statistics dashboard.
3. **Sub-project 3: Mobile Application** (`mobile/`) — Flutter cross-platform mobile app for citizen reporting (camera, GPS location, history, and AI detection).

This specification focuses specifically on **Sub-project 1: Machine Learning & FastAPI Service**, which serves as the foundation for AI detection across the web and mobile interfaces.

---

## 2. Requirements & Success Criteria

### Functional Requirements
- Classify waste images into at least 3 distinct categories:
  1. `Organic Waste`
  2. `Plastic Waste`
  3. `Paper Waste`
- Curated dataset containing at least 150 images total (target: ~240–300 images across the 3 categories for robust generalization).
- Train and test using Python 3.10 and TensorFlow / Keras.
- Comprehensive model evaluation:
  - Test set accuracy calculation (Mandatory target: $\ge 80\%$).
  - Confusion Matrix visualization plot (`confusion_matrix.png`).
  - Training and validation accuracy/loss curves plot (`training_curves.png`).
  - Classification report containing precision, recall, and F1-score (`classification_report.txt`).
- Expose a REST API for inference using FastAPI and Uvicorn.

### Deliverables for this Sub-project
- Complete training, evaluation, and dataset preparation scripts.
- Exported trained model (`models/waste_classifier.keras` and/or `waste_classifier.h5`).
- Visual evaluation artifacts (`reports/confusion_matrix.png`, `reports/training_curves.png`, `reports/classification_report.txt`).
- Curated dataset with source attribution and provenance in `README.md`.
- FastAPI service with interactive Swagger documentation (`/docs`) and `/predict` endpoint.

---

## 3. Directory & File Structure

The `ml-service/` sub-project will be structured as follows:

```
ml-service/
├── dataset/
│   ├── raw/                       # Staging directory for fetched source images
│   ├── train/                     # Training split (70%)
│   │   ├── organic/
│   │   ├── plastic/
│   │   └── paper/
│   ├── val/                       # Validation split (15%)
│   │   ├── organic/
│   │   ├── plastic/
│   │   └── paper/
│   └── test/                      # Testing split (15%)
│       ├── organic/
│       ├── plastic/
│       └── paper/
├── models/
│   └── waste_classifier.keras     # Saved trained model weights
├── reports/
│   ├── confusion_matrix.png       # Generated confusion matrix plot
│   ├── training_curves.png        # Generated accuracy and loss curve plot
│   └── classification_report.txt  # Detailed metrics (Precision, Recall, F1)
├── scripts/
│   ├── prepare_dataset.py         # Automates download, curation, validation & split
│   ├── train.py                   # Transfer learning training pipeline
│   └── evaluate.py                # Standalone evaluation and metrics generator
├── app/
│   ├── __init__.py
│   ├── config.py                  # API settings, paths, class mappings
│   ├── predictor.py               # Model loading, image preprocessing & inference
│   └── main.py                    # FastAPI application and route endpoints
├── requirements.txt               # Python package dependencies
└── README.md                      # Setup guide, API docs, dataset sources & metrics
```

---

## 4. Technical Specifications

### 4.1. Dataset Preparation (`scripts/prepare_dataset.py`)
- **Source**: Curated subsets from established open datasets (e.g. TrashNet / Kaggle Waste Classification dataset).
- **Target Size**: Minimum 80 images per class ($\approx 240$ images total).
- **Data Splitting**:
  - 70% Training (`train/`)
  - 15% Validation (`val/`)
  - 15% Testing (`test/`)
- **Sanitization**: Image verification to filter corrupted files, ensuring standard RGB format (JPEG/PNG).

### 4.2. Model Architecture & Training (`scripts/train.py`)
- **Base Architecture**: MobileNetV2 (ImageNet weights, frozen initial base).
- **Input Dimensions**: $224 \times 224 \times 3$.
- **Augmentation Pipeline**:
  - Random horizontal flip
  - Random rotation ($\pm 15^\circ$)
  - Random zoom ($\pm 10\%$)
- **Custom Classification Head**:
  - `GlobalAveragePooling2D()`
  - `Dropout(0.3)`
  - `Dense(128, activation='relu')`
  - `BatchNormalization()`
  - `Dropout(0.2)`
  - `Dense(3, activation='softmax')`
- **Hyperparameters**:
  - Loss: `categorical_crossentropy`
  - Optimizer: `Adam(learning_rate=1e-4)`
  - Batch size: 16 or 32
  - Max Epochs: 20 (with `EarlyStopping(patience=5, restore_best_weights=True)`)
  - Target: Test accuracy $\ge 80\%$.

### 4.3. Evaluation Pipeline (`scripts/evaluate.py`)
- Evaluates saved model strictly against unseen images in `dataset/test/`.
- Computes:
  - Accuracy, macro and weighted averages.
  - Per-class precision, recall, and F1-score via `sklearn.metrics.classification_report`.
  - Confusion matrix rendered via `matplotlib` and `seaborn`.

### 4.4. FastAPI REST API (`app/main.py`)
- **CORS Middleware**: Allows requests from any origin (`*`) to support local development with Laravel Web and Flutter.
- **Endpoints**:
  - `GET /`: Basic API metadata and health status.
  - `GET /health`: Model status, device info, loaded classes.
  - `POST /predict`:
    - Accepts multipart image file (`UploadFile`).
    - Validates file type (`image/jpeg`, `image/png`, `image/webp`).
    - Preprocesses image using `tensorflow.keras.applications.mobilenet_v2.preprocess_input`.
    - Returns JSON structure:
      ```json
      {
        "status": "success",
        "data": {
          "label": "Plastic Waste",
          "category": "Anorganik",
          "confidence": 0.942,
          "probabilities": {
            "Organic Waste": 0.021,
            "Plastic Waste": 0.942,
            "Paper Waste": 0.037
          }
        }
      }
      ```
  - `GET /docs`: Swagger documentation.

---

## 5. Testing & Verification Plan

1. **Environment Setup**:
   - Create Python virtual environment (`python -m venv .venv`).
   - Install dependencies from `requirements.txt`.
2. **Dataset Verification**:
   - Execute `prepare_dataset.py` and verify image count $\ge 150$ total across `train`, `val`, and `test`.
3. **Training & Metrics Verification**:
   - Execute `train.py` and ensure convergence without severe overfitting.
   - Run `evaluate.py` and verify testing accuracy $\ge 80\%$.
   - Confirm generation of `reports/confusion_matrix.png`, `reports/training_curves.png`, and `reports/classification_report.txt`.
4. **API Testing**:
   - Launch FastAPI with `uvicorn app.main:app --port 8000`.
   - Send sample test images via `curl` / HTTP POST to `http://localhost:8000/predict`.
   - Verify proper JSON response and accurate classification.
