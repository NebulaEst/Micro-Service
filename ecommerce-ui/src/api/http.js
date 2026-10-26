const TIMEOUT_MS = 5000;

export class ApiError extends Error {
    constructor(message, {status = 0, error = null} = {}) {
        super(message);
        this.name   = 'ApiError';
        this.status = status;
        this.error  = error;
    }
}

export async function request(baseUrl, path, { method = 'GET', body}={}) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), TIMEOUT_MS);

    try{
        const response = await fetch (`${baseUrl}${path}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body ? {'Content-type': 'application/json'} : {}),
            },
            body: body ? JSON.stringify(body) : underfined,
            signal: controller.signal
        });

        const playload = await response.json().catch(() => null)

        if (!response.ok){
            throw new ApiError(playload?.message ?? `Permintaan gagal (${response.status})`, {
                status: response.status,
                errors: playload?.errors ?? null
            });
        }

        return playload
    } catch (error) {
        if(error instanceof ApiError) throw error
        if (error.name === 'AbortError') throw new ApiError('Server terlalu lama menjawab')
            throw new ApiError ('Tidak dapat terhubung ke server')
    }finally{
        clearTimeout(timer)
    }
}