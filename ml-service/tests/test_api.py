import pytest
from httpx import AsyncClient, ASGITransport
import io
import sys
from pathlib import Path
from PIL import Image

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from app.main import app

@pytest.mark.asyncio
async def test_root_endpoint():
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.get("/")
    assert response.status_code == 200
    assert response.json()["status"] == "online"

@pytest.mark.asyncio
async def test_health_endpoint():
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.get("/health")
    assert response.status_code == 200
    assert response.json()["model_loaded"] is True

@pytest.mark.asyncio
async def test_predict_valid_image():
    # Create test synthetic image
    img = Image.new('RGB', (224, 224), color=(73, 109, 137))
    buf = io.BytesIO()
    img.save(buf, format='JPEG')
    buf.seek(0)
    
    files = {"file": ("test.jpg", buf, "image/jpeg")}
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.post("/predict", files=files)
        
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "success"
    assert "label" in data["data"]
    assert "confidence" in data["data"]
    assert data["data"]["confidence"] > 0
    assert "category" in data["data"]
    assert "probabilities" in data["data"]

@pytest.mark.asyncio
async def test_predict_invalid_file_format():
    files = {"file": ("document.txt", io.BytesIO(b"not an image"), "text/plain")}
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.post("/predict", files=files)
    assert response.status_code == 400
    assert "not a supported image" in response.json()["detail"].lower()

@pytest.mark.asyncio
async def test_predict_file_size_limit_rejection(monkeypatch):
    from app import config
    # Temporarily set max image size to 50 bytes for testing limit enforcement
    monkeypatch.setattr(config, "MAX_IMAGE_SIZE_BYTES", 50)
    
    large_payload = b"A" * 100
    files = {"file": ("large.jpg", io.BytesIO(large_payload), "image/jpeg")}
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.post("/predict", files=files)
        
    assert response.status_code == 413
    assert "exceeds maximum limit" in response.json()["detail"].lower()

@pytest.mark.asyncio
async def test_predict_auth_header_enforcement(monkeypatch):
    from app import config
    # Require internal secret
    monkeypatch.setattr(config, "INTERNAL_API_KEY", "secret-test-token-123")
    
    img = Image.new('RGB', (224, 224), color=(10, 20, 30))
    buf = io.BytesIO()
    img.save(buf, format='JPEG')
    buf.seek(0)
    files = {"file": ("test.jpg", buf, "image/jpeg")}

    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        # 1. Missing header -> 401 Unauthorized
        res_unauth = await ac.post("/predict", files=files)
        assert res_unauth.status_code == 401
        assert "unauthorized" in res_unauth.json()["detail"].lower()

        # 2. Valid header -> 200 OK
        buf.seek(0)
        files = {"file": ("test.jpg", buf, "image/jpeg")}
        res_auth = await ac.post("/predict", files=files, headers={"X-Internal-Secret": "secret-test-token-123"})
        assert res_auth.status_code == 200

