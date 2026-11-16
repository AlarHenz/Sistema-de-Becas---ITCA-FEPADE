const API_URL = import.meta.env.DEV
  ? '/api/index.php'
  : new URL('../api/index.php', document.baseURI).toString();

let csrfToken = '';

export function updateCsrfToken(token) {
  csrfToken = token || '';
}

export function apiUrl(route, params = {}) {
  const url = new URL(API_URL, window.location.href);
  url.searchParams.set('route', route);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      url.searchParams.set(key, String(value));
    }
  });
  return url.toString();
}

export async function apiRequest(route, options = {}) {
  const { method = 'GET', body, params } = options;
  const headers = { Accept: 'application/json' };
  const request = { method, headers, credentials: 'include' };

  if (body instanceof FormData) {
    request.body = body;
  } else if (body !== undefined) {
    headers['Content-Type'] = 'application/json';
    request.body = JSON.stringify(body);
  }
  if (!['GET', 'HEAD'].includes(method.toUpperCase())) {
    headers['X-CSRF-Token'] = csrfToken;
  }

  const response = await fetch(apiUrl(route, params), request);
  const contentType = response.headers.get('content-type') || '';
  if (!contentType.includes('application/json')) {
    throw new Error('El servidor devolvió una respuesta inesperada.');
  }
  const result = await response.json();
  if (!response.ok) {
    throw new Error(result.message || 'No se pudo completar la solicitud.');
  }
  if (result.csrfToken) {
    updateCsrfToken(result.csrfToken);
  }
  return result;
}
