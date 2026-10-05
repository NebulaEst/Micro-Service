const productModel = require('../models/productModel');

const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const BASE64_PATTERN = /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/;

function validateImage(image) {
    if (typeof image !== 'string' || image.length === 0) {
        return 'Field image wajib diisi';
    }

    if (!BASE64_PATTERN.test(image)) {
        return 'Field image harus berupa Base64 yang valid';
    }

    const decodedImage = Buffer.from(image, 'base64');
    if (decodedImage.toString('base64') !== image) {
        return 'Field image harus berupa Base64 yang valid';
    }

    if (decodedImage.length > MAX_IMAGE_SIZE) {
        return 'Ukuran image maksimal 2 MB';
    }

    return null;
}

function validateProductFields(product) {
    if (typeof product.name !== 'string' || product.name.trim() === '') {
        return 'Field name wajib diisi';
    }

    if (product.price === undefined || product.price === null || product.price === '' ||
        (typeof product.price === 'string' && product.price.trim() === '') ||
        !Number.isFinite(Number(product.price)) || Number(product.price) < 0) {
        return 'Field price harus berupa angka nol atau lebih';
    }

    if (product.stock !== undefined &&
        ((typeof product.stock === 'string' && product.stock.trim() === '') ||
        !Number.isInteger(Number(product.stock)) || Number(product.stock) < 0)) {
        return 'Field stock harus berupa bilangan bulat nol atau lebih';
    }

    if (product.description !== undefined && product.description !== null &&
        typeof product.description !== 'string') {
        return 'Field description harus berupa teks';
    }

    return null;
}

function validateProductRequest(req, res) {
    const product = req.body;
    if (!product || typeof product !== 'object' || Array.isArray(product)) {
        res.status(400).json({ message: 'Request body harus berupa object JSON' });
        return null;
    }

    const imageError = validateImage(product.image);
    if (imageError) {
        res.status(400).json({ message: imageError });
        return null;
    }

    const fieldsError = validateProductFields(product);
    if (fieldsError) {
        res.status(400).json({ message: fieldsError });
        return null;
    }

    return {
        name: product.name.trim(),
        description: product.description ?? null,
        price: Number(product.price),
        stock: product.stock === undefined ? 0 : Number(product.stock),
        image: product.image
    };
}

//GET ambil semua product
async function index(req, res) {
    try {
        const products = await productModel.getAllProducts();
        res.json({
            message : "Berhasil mengambil data product",
            data: products
        });
    } catch (error) {
        res.status(500).json({
            message : "Gagal mengambil data product",
            error: error.message
        })
    }
}

async function show(req, res) {
    try {
        const product = await productModel.getProductById(req.params.id);
        if (!product) {
            return res.status(404).json({ message: 'Product tidak ditemukan' });
        }

        return res.json({
            message: 'Berhasil mengambil data product',
            data: product
        });
    } catch (error) {
        return res.status(500).json({
            message: 'Gagal mengambil data product',
            error: error.message
        });
    }
}

async function create(req, res) {
    const product = validateProductRequest(req, res);
    if (!product) return;

    try {
        const createdProduct = await productModel.createProduct(product);
        return res.status(201).json({
            message: 'Berhasil menambahkan product',
            data: createdProduct
        });
    } catch (error) {
        return res.status(500).json({
            message: 'Gagal menambahkan product',
            error: error.message
        });
    }
}

async function update(req, res) {
    const product = validateProductRequest(req, res);
    if (!product) return;

    try {
        const updatedProduct = await productModel.updateProduct(req.params.id, product);
        if (!updatedProduct) {
            return res.status(404).json({ message: 'Product tidak ditemukan' });
        }

        return res.json({
            message: 'Berhasil memperbarui product',
            data: updatedProduct
        });
    } catch (error) {
        return res.status(500).json({
            message: 'Gagal memperbarui product',
            error: error.message
        });
    }
}

module.exports = { index, show, create, update };