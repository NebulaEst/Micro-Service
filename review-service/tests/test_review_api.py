import os
import unittest
from unittest.mock import patch

from app import create_app
from app.exceptions import (
    DuplicateReviewError,
    ProductNotFoundError,
    ProductServiceUnavailableError,
    ReviewNotFoundError,
)

REVIEW_ID = "665f1c2e9a1b2c3d4e5f6a7b"


class ReviewApiTests(unittest.TestCase):
    def setUp(self):
        self.debug_environment = patch.dict(os.environ, {"FLASK_DEBUG": "0"})
        self.debug_environment.start()
        self.addCleanup(self.debug_environment.stop)
        with patch("app.init_db"):
            self.app = create_app()
        self.app.config["TESTING"] = True
        self.client = self.app.test_client()
        self.review = {
            "id": REVIEW_ID,
            "product_id": 1,
            "user_id": 7,
            "rating": 5,
            "comment": "Bagus",
            "created_at": "2026-10-12T02:00:00+00:00",
            "updated_at": "2026-10-12T02:00:00+00:00",
        }

    def test_health_check(self):
        response = self.client.get("/health")

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json, {"status": "ok", "service": "review-service"})

    def test_list_reviews_with_summary(self):
        expected = {
            "product_id": 1,
            "summary": {"average_rating": 5, "total_reviews": 1},
            "reviews": [self.review],
        }
        with patch(
            "app.controllers.review_controller.review_service.get_product_reviews",
            return_value=expected,
        ) as get_reviews:
            response = self.client.get("/reviews/product/1")

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json["data"], expected)
        get_reviews.assert_called_once_with(1)

    def test_list_reviews_rejects_nonpositive_product_id(self):
        response = self.client.get("/reviews/product/0")

        self.assertEqual(response.status_code, 422)
        self.assertIn("product_id", response.json["errors"])

    def test_get_single_review(self):
        with patch(
            "app.controllers.review_controller.review_service.get_review",
            return_value=self.review,
        ) as get_review:
            response = self.client.get(f"/reviews/{REVIEW_ID}")

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json["data"], self.review)
        get_review.assert_called_once_with(REVIEW_ID)

    def test_create_review(self):
        payload = {"product_id": 1, "user_id": 7, "rating": 5}
        with patch(
            "app.controllers.review_controller.review_service.create_review",
            return_value=self.review,
        ) as create_review:
            response = self.client.post("/reviews", json=payload)

        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.json["data"], self.review)
        create_review.assert_called_once_with({**payload, "comment": ""})

    def test_create_review_reports_all_invalid_fields(self):
        response = self.client.post(
            "/reviews",
            json={"product_id": 0, "user_id": True, "rating": 6, "comment": None},
        )

        self.assertEqual(response.status_code, 422)
        self.assertEqual(
            set(response.json["errors"]),
            {"product_id", "user_id", "rating", "comment"},
        )

    def test_create_review_requires_product_and_user_ids(self):
        response = self.client.post("/reviews", json={"rating": 5})

        self.assertEqual(response.status_code, 422)
        self.assertEqual(set(response.json["errors"]), {"product_id", "user_id"})

    def test_create_review_rejects_malformed_json(self):
        response = self.client.post(
            "/reviews",
            data='{"rating":',
            content_type="application/json",
        )

        self.assertEqual(response.status_code, 422)
        self.assertIn("body", response.json["errors"])

    def test_create_review_rejects_long_comment(self):
        response = self.client.post(
            "/reviews",
            json={
                "product_id": 1,
                "user_id": 7,
                "rating": 5,
                "comment": "x" * 1001,
            },
        )

        self.assertEqual(response.status_code, 422)
        self.assertIn("comment", response.json["errors"])

    def test_create_review_maps_missing_product_to_validation_error(self):
        with patch(
            "app.controllers.review_controller.review_service.create_review",
            side_effect=ProductNotFoundError(999),
        ):
            response = self.client.post(
                "/reviews",
                json={"product_id": 999, "user_id": 7, "rating": 5},
            )

        self.assertEqual(response.status_code, 422)
        self.assertIn("product_id", response.json["errors"])

    def test_create_review_maps_duplicate_to_conflict(self):
        with patch(
            "app.controllers.review_controller.review_service.create_review",
            side_effect=DuplicateReviewError(7, 1),
        ):
            response = self.client.post(
                "/reviews",
                json={"product_id": 1, "user_id": 7, "rating": 5},
            )

        self.assertEqual(response.status_code, 409)

    def test_create_review_maps_product_dependency_failure_to_503(self):
        with patch(
            "app.controllers.review_controller.review_service.create_review",
            side_effect=ProductServiceUnavailableError("service timeout"),
        ):
            response = self.client.post(
                "/reviews",
                json={"product_id": 1, "user_id": 7, "rating": 5},
            )

        self.assertEqual(response.status_code, 503)
        self.assertNotIn("error", response.json)

    def test_update_review(self):
        payload = {"rating": 4, "comment": "Tetap bagus"}
        with patch(
            "app.controllers.review_controller.review_service.update_review",
            return_value=self.review,
        ) as update_review:
            response = self.client.put(f"/reviews/{REVIEW_ID}", json=payload)

        self.assertEqual(response.status_code, 200)
        update_review.assert_called_once_with(REVIEW_ID, payload)

    def test_update_review_requires_a_field_and_valid_values(self):
        empty_response = self.client.put(f"/reviews/{REVIEW_ID}", json={})
        invalid_response = self.client.put(
            f"/reviews/{REVIEW_ID}",
            json={"rating": True, "comment": "x" * 1001},
        )

        self.assertEqual(empty_response.status_code, 422)
        self.assertEqual(invalid_response.status_code, 422)
        self.assertEqual(set(invalid_response.json["errors"]), {"rating", "comment"})

    def test_delete_review(self):
        with patch(
            "app.controllers.review_controller.review_service.delete_review"
        ) as delete_review:
            response = self.client.delete(f"/reviews/{REVIEW_ID}")

        self.assertEqual(response.status_code, 200)
        delete_review.assert_called_once_with(REVIEW_ID)

    def test_missing_and_malformed_review_ids_return_404(self):
        with patch(
            "app.controllers.review_controller.review_service.get_review",
            side_effect=ReviewNotFoundError("abc"),
        ):
            missing = self.client.get(f"/reviews/{REVIEW_ID}")
            malformed = self.client.get("/reviews/abc")

        self.assertEqual(missing.status_code, 404)
        self.assertEqual(malformed.status_code, 404)

    def test_unknown_path_and_method_return_json_errors(self):
        not_found = self.client.get("/unknown")
        invalid_product_id = self.client.get("/reviews/product/abc")
        method_not_allowed = self.client.patch("/reviews")

        self.assertEqual(not_found.status_code, 404)
        self.assertEqual(invalid_product_id.status_code, 404)
        self.assertEqual(method_not_allowed.status_code, 405)
        self.assertIsInstance(not_found.json, dict)
        self.assertIsInstance(invalid_product_id.json, dict)
        self.assertIsInstance(method_not_allowed.json, dict)


if __name__ == "__main__":
    unittest.main()
