import logging
import os

from dotenv import load_dotenv
from flask import Flask, jsonify

from app.database import init_db
from app.routes.review_routes import review_bp


def create_app():
    load_dotenv()

    app = Flask(__name__)
    app.config["DEBUG"] = os.getenv("FLASK_DEBUG", "0").lower() in {"1", "true", "yes"}
    app.logger.setLevel(logging.INFO)

    init_db()
    app.register_blueprint(review_bp)

    @app.get("/health")
    def health():
        return jsonify({"status": "ok", "service": "review-service"})

    @app.errorhandler(404)
    def not_found(_error):
        return jsonify({"message": "Endpoint tidak ditemukan"}), 404

    @app.errorhandler(405)
    def method_not_allowed(_error):
        return jsonify({"message": "Method tidak diizinkan untuk endpoint ini"}), 405

    return app
