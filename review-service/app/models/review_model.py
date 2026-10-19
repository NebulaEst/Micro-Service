from pymongo import DESCENDING, ReturnDocument

from app.database import get_collection


def _serialize(document):
    if document is None:
        return None
    return {
        "id": str(document["_id"]),
        "product_id": document["product_id"],
        "user_id": document["user_id"],
        "rating": document["rating"],
        "comment": document["comment"],
        "created_at": document["created_at"].isoformat(),
        "updated_at": document["updated_at"].isoformat(),
    }


def find_by_product(product_id):
    cursor = get_collection().find({"product_id": product_id}).sort(
        [("created_at", DESCENDING), ("_id", DESCENDING)]
    )
    return [_serialize(document) for document in cursor]


def find_by_id(object_id):
    return _serialize(get_collection().find_one({"_id": object_id}))


def insert(document):
    result = get_collection().insert_one(document)
    return find_by_id(result.inserted_id)


def update(object_id, fields):
    document = get_collection().find_one_and_update(
        {"_id": object_id},
        {"$set": fields},
        return_document=ReturnDocument.AFTER,
    )
    return _serialize(document)


def delete(object_id):
    return get_collection().delete_one({"_id": object_id}).deleted_count > 0


def rating_summary(product_id):
    pipeline = [
        {"$match": {"product_id": product_id}},
        {
            "$group": {
                "_id": None,
                "average": {"$avg": "$rating"},
                "total": {"$sum": 1},
            }
        },
    ]
    result = list(get_collection().aggregate(pipeline))
    if not result or not result[0]["total"]:
        return {"average_rating": 0, "total_reviews": 0}
    return {
        "average_rating": round(result[0]["average"], 2),
        "total_reviews": result[0]["total"],
    }
