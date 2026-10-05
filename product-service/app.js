const express = require ('express');
const cors = require ('cors');
const productRoutes = require('./routes/productRoutes');

const app = express();

app.use(cors());
app.use(express.json({ limit: '3mb' }));

//endpoint for healtcheck
app.get('/health', (req , res) => {
    res.json({
        status : "ok",
        service: "product-service"
    });
});

app.use("/products", productRoutes);

app.use((error, req, res, next) => {
    if (error.type === 'entity.too.large') {
        return res.status(413).json({
            message: 'Ukuran request body maksimal 3 MB'
        });
    }

    if (error instanceof SyntaxError && error.status === 400 && 'body' in error) {
        return res.status(400).json({
            message: 'Format JSON tidak valid'
        });
    }

    next(error);
});

//unknown path
app.use((req,res) => {
    res.status(404).json({
        message: "endpoint tidak dikenal"
    });
});

module.exports = app;