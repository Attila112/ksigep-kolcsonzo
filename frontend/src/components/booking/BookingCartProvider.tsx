"use client";

import {
    createContext,
    useCallback,
    useContext,
    useEffect,
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

const EMPTY_CART: BookingCartState = {
    period: null,
    items: [],
};

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
 */
function loadStoredCart(): BookingCartState {
    try {
        const storedValue =
            window.sessionStorage.getItem(
                BOOKING_CART_STORAGE_KEY
            );

        if (!storedValue) {
            return EMPTY_CART;
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

        return EMPTY_CART;
    }
}

/**
 * Elmenti a teljes foglalási kosarat.
 */
function saveCart(
    state: BookingCartState
): void {
    window.sessionStorage.setItem(
        BOOKING_CART_STORAGE_KEY,
        JSON.stringify(state)
    );
}

export function BookingCartProvider({
    children,
}: BookingCartProviderProps) {
    /**
     * Fontos:
     *
     * A szerver és a kliens első renderje is
     * ugyanazzal az üres kosárral indul.
     *
     * Ez akadályozza meg a hydration mismatch-et.
     */
    const [cart, setCart] =
        useState<BookingCartState>(
            EMPTY_CART
        );

    /**
     * A sessionStorage tartalmát csak a
     * kliens hydration után töltjük be.
     *
     * A queueMicrotask miatt nem történik
     * szinkron setState közvetlenül az effectben.
     */
    useEffect(() => {
        let cancelled = false;

        queueMicrotask(() => {
            if (cancelled) {
                return;
            }

            setCart(loadStoredCart());
        });

        return () => {
            cancelled = true;
        };
    }, []);

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

                const nextCart: BookingCartState =
                {
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
        setCart(EMPTY_CART);

        window.sessionStorage.removeItem(
            BOOKING_CART_STORAGE_KEY
        );
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