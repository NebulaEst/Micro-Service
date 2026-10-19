import traceback

from flask import current_app, jsonify, request

from app.exceptions import (
    DuplicateReviewError,
    ProductNotFoundError,
    ProductServiceUnavailableError,
    ReviewNotFoundError,
    ValidationError,
)
from app.schemas.review_schema import validate_create_review, validate_update_review
from app.services import review_service


def list_by_product(product_id):
    if product_id < 1:
        return jsonify({
            "message": "Data yang dikirim tidak valid",
            "errors": {"product_id": ["Field product_id harus berupa angka bulat minimal 1"]},
        }), 422
    try:
        data = review_service.get_product_reviews(product_id)
        return jsonify({"message": "Berhasil mengambil review produk", "data": data})
    except Exception as error:
        return _error_response(error, "mengambil review produk")


def show(review_id):
    try:
        review = review_service.get_review(review_id)
        return jsonify({"message": "Berhasil mengambil review", "data": review})
    except ReviewNotFoundError as error:
        return jsonify({"message": str(error)}), 404
    except Exception as error:
        return _error_response(error, "mengambil review")


def store():
    try:
        data = validate_create_review(request.get_json(silent=True))
        review = review_service.create_review(data)
        return jsonify({"message": "Review berhasil dibuat", "data": review}), 201
    except ValidationError as error:
        return _validation_response(error)
    except ProductNotFoundError as error:
        return jsonify({
            "message": "Data yang dikirim tidak valid",
            "errors": {"product_id": [str(error)]},
        }), 422
    except ProductServiceUnavailableError as error:
        return _unavailable_response(error)
    except DuplicateReviewError as error:
        return jsonify({"message": str(error)}), 409
    except Exception as error:
        return _error_response(error, "membuat review")


def update(review_id):
    try:
        data = validate_update_review(request.get_json(silent=True))
        review = review_service.update_review(review_id, data)
        return jsonify({"message": "Review berhasil diperbarui", "data": review})
    except ValidationError as error:
        return _validation_response(error)
    except ReviewNotFoundError as error:
        return jsonify({"message": str(error)}), 404
    except Exception as error:
        return _error_response(error, "memperbarui review")


def destroy(review_id):
    try:
        review_service.delete_review(review_id)
        return jsonify({"message": "Review berhasil dihapus"})
    except ReviewNotFoundError as error:
        return jsonify({"message": str(error)}), 404
    except Exception as error:
        return _error_response(error, "menghapus review")


def _validation_response(error):
    return jsonify({"message": str(error), "errors": error.errors}), 422


def _unavailable_response(error):
    current_app.logger.warning(
        "Product service bermasalah: %s (penyebab: %s)",
        error,
        error.__cause__,
    )
    body = {"message": "Product service tidak dapat dihubungi, coba lagi nanti"}
    if current_app.debug:
        body["error"] = str(error)
        body["cause"] = str(error.__cause__) if error.__cause__ else None
    return jsonify(body), 503


def _error_response(error, action):
    current_app.logger.exception("Gagal %s", action)
    body = {"message": f"Gagal {action}"}
    if current_app.debug:
        frame = traceback.extract_tb(error.__traceback__)[-1]
        body["error"] = str(error)
        body["location"] = f"{frame.filename}:{frame.lineno}"
    return jsonify(body), 500
