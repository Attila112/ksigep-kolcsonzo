"use client";

import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
    type ReactNode,
} from "react";

import type {
    BookingCartItem,
    BookingCartPeriod,
    BookingCartState,
} from "@/types/bookingCart";

const BOOKING_CART_STORAGE_KEY =
    "booking-cart";

type AddBookingCartItemInput = {
    period: BookingCartPeriod;
    item: BookingCartItem;
};

type BookingCartContextValue =
    BookingCartState & {
        addItem: (
            input: AddBookingCartItemInput
        ) => void;
        removeItem: (
            productId: number
        ) => void;
        clearCart: () => void;
        hasProduct: (
            productId: number
        ) => boolean;
        updateQuantity: (
            productId: number,
            quantity: number
        ) => void;
    };

const BookingCartContext =
    createContext<BookingCartContextValue | null>(
        null
    );

type BookingCartProviderProps = {
    children: ReactNode;
};

/**
 * Betölti a sessionStorage-ban lévő
 * foglalási kosarat.
 *
 * Szerver oldali renderelésnél a sessionStorage
 * nem érhető el, ezért ilyenkor üres kosarat adunk.
 */
function loadStoredCart(): BookingCartState {
    if (typeof window === "undefined") {
        return {
            period: null,
            items: [],
        };
    }

    try {
        const storedValue =
            window.sessionStorage.getItem(
                BOOKING_CART_STORAGE_KEY
            );

        if (!storedValue) {
            return {
                period: null,
                items: [],
            };
        }

        const storedCart =
            JSON.parse(
                storedValue
            ) as BookingCartState;

        return {
            period:
                storedCart.period ?? null,
            items: Array.isArray(
                storedCart.items
            )
                ? storedCart.items
                : [],
        };
    } catch {
        window.sessionStorage.removeItem(
            BOOKING_CART_STORAGE_KEY
        );

        return {
            period: null,
            items: [],
        };
    }
}

/**
 * Elmenti a teljes foglalási kosarat.
 */
function saveCart(
    state: BookingCartState
): void {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(
        BOOKING_CART_STORAGE_KEY,
        JSON.stringify(state)
    );
}

export function BookingCartProvider({
    children,
}: BookingCartProviderProps) {
    const [cart, setCart] =
        useState<BookingCartState>(
            loadStoredCart
        );

    const addItem = useCallback(
        ({
            period: newPeriod,
            item,
        }: AddBookingCartItemInput) => {
            setCart((currentCart) => {
                if (currentCart.period) {
                    const samePeriod =
                        currentCart.period
                            .startDate ===
                        newPeriod.startDate &&
                        currentCart.period
                            .endDate ===
                        newPeriod.endDate;

                    if (!samePeriod) {
                        throw new Error(
                            "BOOKING_CART_PERIOD_MISMATCH"
                        );
                    }
                }

                const existingItem =
                    currentCart.items.find(
                        (currentItem) =>
                            currentItem.productId ===
                            item.productId
                    );

                const nextItems =
                    existingItem
                        ? currentCart.items.map(
                            (
                                currentItem
                            ) =>
                                currentItem.productId ===
                                    item.productId
                                    ? item
                                    : currentItem
                        )
                        : [
                            ...currentCart.items,
                            item,
                        ];

                const nextCart: BookingCartState =
                {
                    period:
                        currentCart.period ??
                        newPeriod,
                    items: nextItems,
                };

                saveCart(nextCart);

                return nextCart;
            });
        },
        []
    );
    const updateQuantity = useCallback(
        (
            productId: number,
            quantity: number
        ) => {
            if (quantity < 1) {
                return;
            }

            setCart((currentCart) => {
                const nextItems =
                    currentCart.items.map(
                        (item) =>
                            item.productId ===
                                productId
                                ? {
                                    ...item,
                                    quantity,
                                }
                                : item
                    );

                const nextCart: BookingCartState = {
                    ...currentCart,
                    items: nextItems,
                };

                saveCart(nextCart);

                return nextCart;
            });
        },
        []
    );

    const removeItem = useCallback(
        (productId: number) => {
            setCart((currentCart) => {
                const nextItems =
                    currentCart.items.filter(
                        (item) =>
                            item.productId !==
                            productId
                    );

                const nextCart: BookingCartState =
                {
                    period:
                        nextItems.length === 0
                            ? null
                            : currentCart.period,
                    items: nextItems,
                };

                saveCart(nextCart);

                return nextCart;
            });
        },
        []
    );

    const clearCart = useCallback(() => {
        const emptyCart: BookingCartState =
        {
            period: null,
            items: [],
        };

        setCart(emptyCart);

        if (
            typeof window !== "undefined"
        ) {
            window.sessionStorage.removeItem(
                BOOKING_CART_STORAGE_KEY
            );
        }
    }, []);

    const hasProduct = useCallback(
        (productId: number) =>
            cart.items.some(
                (item) =>
                    item.productId ===
                    productId
            ),
        [cart.items]
    );

    const value = useMemo(
        () => ({
            period: cart.period,
            items: cart.items,
            addItem,
            updateQuantity,
            removeItem,
            clearCart,
            hasProduct,
        }),
        [
            cart,
            addItem,
            updateQuantity,
            removeItem,
            clearCart,
            hasProduct,
        ]
    );

    return (
        <BookingCartContext.Provider
            value={value}
        >
            {children}
        </BookingCartContext.Provider>
    );
}

export function useBookingCart() {
    const context = useContext(
        BookingCartContext
    );

    if (!context) {
        throw new Error(
            "useBookingCart must be used inside BookingCartProvider"
        );
    }

    return context;
}