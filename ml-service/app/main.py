from typing import Optional
from fastapi import FastAPI, UploadFile, File, Header, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from app.predictor import predictor
from app import config

app = FastAPI(
    title="Smart Waste AI Detection API",
    description="Inference REST API for classifying waste into Organic, Plastic, and Paper categories.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc"
)

# Secure CORS configuration
is_wildcard = "*" in config.CORS_ORIGINS
app.add_middleware(
    CORSMiddleware,
    allow_origins=config.CORS_ORIGINS,
    allow_credentials=not is_wildcard,
    allow_methods=["GET", "POST", "OPTIONS"],
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
        "classes": [config.CLASS_MAPPINGS[i]["name"] for i in range(len(config.CLASS_MAPPINGS))]
    }

@app.post("/predict", tags=["Inference"])
async def predict_waste(
    file: UploadFile = File(..., description="Waste image file (JPEG, PNG, WebP)"),
    x_internal_secret: Optional[str] = Header(None, alias="X-Internal-Secret")
):
    # 1. Verify internal API key if configured
    if config.INTERNAL_API_KEY and x_internal_secret != config.INTERNAL_API_KEY:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Unauthorized: Missing or invalid internal API secret header."
        )

    # 2. Validate content type
    if file.content_type not in config.ALLOWED_CONTENT_TYPES:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Uploaded file is not a supported image format. Allowed: {sorted(list(config.ALLOWED_CONTENT_TYPES))}"
        )
        
    # 3. Check model readiness
    if predictor.model is None:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Machine learning model is currently not loaded or unavailable."
        )

    # 4. Read image bytes with max size limit and run inference
    try:
        content = await file.read(config.MAX_IMAGE_SIZE_BYTES + 1)
        if len(content) > config.MAX_IMAGE_SIZE_BYTES:
            raise HTTPException(
                status_code=413,
                detail=f"Uploaded file exceeds maximum limit of {config.MAX_IMAGE_SIZE_BYTES} bytes."
            )

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
    except HTTPException:
        raise
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

