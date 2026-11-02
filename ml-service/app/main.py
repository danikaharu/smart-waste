from fastapi import FastAPI, UploadFile, File, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from app.predictor import predictor
from app.config import ALLOWED_CONTENT_TYPES, CLASS_MAPPINGS

app = FastAPI(
    title="Smart Waste AI Detection API",
    description="Inference REST API for classifying waste into Organic, Plastic, and Paper categories.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc"
)

# Enable CORS for seamless integration with Web Admin (Laravel/SPA) and Mobile (Flutter)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/", tags=["General"])
def root():
    return {
        "service": "smart-waste-ml-service",
        "status": "online",
        "version": "1.0.0",
        "docs": "/docs"
    }

@app.get("/health", tags=["Monitoring"])
def health():
    is_loaded = predictor.model is not None
    return {
        "status": "healthy" if is_loaded else "degraded",
        "model_loaded": is_loaded,
        "classes": [CLASS_MAPPINGS[i]["name"] for i in range(len(CLASS_MAPPINGS))]
    }

@app.post("/predict", tags=["Inference"])
async def predict_waste(file: UploadFile = File(..., description="Waste image file (JPEG, PNG, WebP)")):
    # 1. Validate content type
    if file.content_type not in ALLOWED_CONTENT_TYPES:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Uploaded file is not a supported image format. Allowed: {sorted(list(ALLOWED_CONTENT_TYPES))}"
        )
        
    # 2. Check model readiness
    if predictor.model is None:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Machine learning model is currently not loaded or unavailable."
        )

    # 3. Read image bytes and run inference
    try:
        content = await file.read()
        if len(content) == 0:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Uploaded file is empty."
            )
            
        result = predictor.predict(content)
        return {
            "status": "success",
            "filename": file.filename,
            "data": result
        }
    except ValueError as ve:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=f"Image processing error: {str(ve)}"
        )
    except Exception as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Inference error: {str(e)}"
        )
