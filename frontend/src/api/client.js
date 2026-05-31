const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

async function request(path, options = {}) {
  const isFormData = options.body instanceof FormData
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
      ...options.headers,
    },
  })
  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    const validation = payload.errors ? Object.values(payload.errors).flat().join(' ') : ''
    throw new Error(validation || payload.message || 'Terjadi kesalahan pada server.')
  }

  return payload
}

export const api = {
  get: (path) => request(path),
  post: (path, data) => request(path, { method: 'POST', body: data instanceof FormData ? data : JSON.stringify(data) }),
  put: (path, data) => request(path, { method: 'PUT', body: JSON.stringify(data) }),
  delete: (path, data) => request(path, { method: 'DELETE', body: data ? JSON.stringify(data) : undefined }),
}
