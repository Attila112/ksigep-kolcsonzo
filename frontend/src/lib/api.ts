const API_URL = process.env.NEXT_PUBLIC_API_URL;

if (!API_URL) {
    throw new Error(
        "A NEXT_PUBLIC_API_URL környezeti változó nincs beállítva."
    );
}

type ApiRequestOptions = RequestInit & {
    token?: string | null;
};

export class ApiError extends Error {
    constructor(
        message: string,
        public readonly status: number,
        public readonly errors?: Record<string, string[]>
    ) {
        super(message);
        this.name = "ApiError";
    }
}

export async function apiRequest<T>(
    path: string,
    options: ApiRequestOptions = {}
): Promise<T> {
    const { token, headers, ...requestOptions } = options;

    const response = await fetch(`${API_URL}${path}`, {
        ...requestOptions,
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            ...headers,
            ...(token
                ? {
                      Authorization: `Bearer ${token}`,
                  }
                : {}),
        },
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(
            data?.message ?? "Hiba történt az API-kérés során.",
            response.status,
            data?.errors
        );
    }

    return data as T;
}