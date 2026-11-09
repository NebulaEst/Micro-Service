import { getCart } from './cartApi';
import {getProduct, getProducts} from './productApi';
import { getProductReviews } from './reviewApi';

//Daftar Produk beserta ringkasan rating.
//1 panggilan ke product-service, lalu 1 panggilan ke review-service untuk SETIAP produk.

export async function getProductsWithRatings() {
    const products = await getProducts();
    
    return  Promise.all(
        products.map(async (product) => {
            try {
                const { summary } = await getProductReviews(product.id)
                return { ...product, ratingSummary: summary }
            } catch {
                //review-service bermasalah: produk tetap ditampilkan, hanya rating-nya kosong
                return { ...product, ratingSummary: null }
            }
        }),
    )
}

//Detail Produk beserta review-nya. Produk wajib ada, review boleh gagal.
export async function getProductDetail(productId) {
    const [productResult, reviewResult] = await Promise.allSettled([
        getProduct(productId),
        getProductReviews(productId)
    ])
    if (productResult.status === 'rejected') throw
    productResult.reason
}