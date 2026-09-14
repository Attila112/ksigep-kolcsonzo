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

type AddBookingCartItemInput = {
    period: BookingCartPeriod;
    item: BookingCartItem;
};

type BookingCartContextValue = BookingCartState & {
    addItem: (
        input: AddBookingCartItemInput
    ) => void;
    removeItem: (productId: number) => void;
    clearCart: () => void;
    hasProduct: (productId: number) => boolean;
};

const BookingCartContext =
    createContext<BookingCartContextValue | null>(
        null
    );

type BookingCartProviderProps = {
    children: ReactNode;
};

export function BookingCartProvider({
    children,
}: BookingCartProviderProps) {
    const [period, setPeriod] =
        useState<BookingCartPeriod | null>(null);

    const [items, setItems] = useState<
        BookingCartItem[]
    >([]);

    const addItem = useCallback(
        ({
            period: newPeriod,
            item,
        }: AddBookingCartItemInput) => {
            setPeriod((currentPeriod) => {
                if (!currentPeriod) {
                    return newPeriod;
                }

                const samePeriod =
                    currentPeriod.startDate ===
                        newPeriod.startDate &&
                    currentPeriod.endDate ===
                        newPeriod.endDate;

                if (!samePeriod) {
                    throw new Error(
                        "BOOKING_CART_PERIOD_MISMATCH"
                    );
                }

                return currentPeriod;
            });

            setItems((currentItems) => {
                const existingItem =
                    currentItems.find(
                        (currentItem) =>
                            currentItem.productId ===
                            item.productId
                    );

                if (existingItem) {
                    return currentItems.map(
                        (currentItem) =>
                            currentItem.productId ===
                            item.productId
                                ? item
                                : currentItem
                    );
                }

                return [
                    ...currentItems,
                    item,
                ];
            });
        },
        []
    );

    const removeItem = useCallback(
        (productId: number) => {
            setItems((currentItems) => {
                const nextItems =
                    currentItems.filter(
                        (item) =>
                            item.productId !==
                            productId
                    );

                if (nextItems.length === 0) {
                    setPeriod(null);
                }

                return nextItems;
            });
        },
        []
    );

    const clearCart = useCallback(() => {
        setItems([]);
        setPeriod(null);
    }, []);

    const hasProduct = useCallback(
        (productId: number) =>
            items.some(
                (item) =>
                    item.productId ===
                    productId
            ),
        [items]
    );

    const value = useMemo(
        () => ({
            period,
            items,
            addItem,
            removeItem,
            clearCart,
            hasProduct,
        }),
        [
            period,
            items,
            addItem,
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