export type AuthUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    active: boolean;
};

export type LoginResponse = {
    message: string;
    token: string;
    user: AuthUser;
};

export type LoginCredentials = {
    email: string;
    password: string;
};