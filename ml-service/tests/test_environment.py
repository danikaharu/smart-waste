import pytest

def test_imports():
    import fastapi
    import uvicorn
    import tensorflow
    import PIL
    import numpy
    import sklearn
    import matplotlib
    import seaborn
    import httpx
    assert fastapi.__version__ is not None
    assert tensorflow.__version__ is not None
