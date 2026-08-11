import { apiRequest } from "@/core/api/api";
import type {
    LoginCredentials,
    LoginResponse,
} from "@/core/auth/types";

export async function login(
    credentials: LoginCredentials
): Promise<LoginResponse> {
    return apiRequest<LoginResponse>("/login", {
        method: "POST",
        body: JSON.stringify(credentials),
    });
}