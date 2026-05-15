<style>
    .sale-cart {
        overflow: hidden;
        border: 1px solid rgb(39 39 42);
        border-radius: 0.75rem;
        background: rgb(24 24 27);
        color: rgb(250 250 250);
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.16);
    }

    .sale-cart-empty {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .sale-cart-empty-icon {
        display: grid;
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto;
        place-items: center;
        border-radius: 999px;
        background: rgb(39 39 42);
        color: rgb(161 161 170);
    }

    .sale-cart-empty-title {
        margin-top: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .sale-cart-empty-text {
        margin-top: 0.25rem;
        color: rgb(161 161 170);
        font-size: 0.875rem;
    }

    .sale-cart-header,
    .sale-cart-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 7rem 9.5rem 7.5rem 2.75rem;
        gap: 1rem;
        align-items: center;
        padding: 0.875rem 1rem;
    }

    .sale-cart-header {
        background: rgb(9 9 11);
        color: rgb(161 161 170);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .sale-cart-row {
        border-top: 1px solid rgb(39 39 42);
    }

    .sale-cart-product {
        min-width: 0;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sale-cart-muted {
        color: rgb(161 161 170);
        font-size: 0.875rem;
    }

    .sale-cart-right {
        text-align: right;
    }

    .sale-cart-center {
        text-align: center;
    }

    .sale-cart-qty {
        display: inline-flex;
        height: 2.25rem;
        overflow: hidden;
        border: 1px solid rgb(63 63 70);
        border-radius: 0.5rem;
        background: rgb(9 9 11);
        vertical-align: middle;
    }

    .sale-cart-qty button,
    .sale-cart-remove {
        display: inline-grid;
        place-items: center;
        border: 0;
        background: transparent;
        color: rgb(212 212 216);
        cursor: pointer;
    }

    .sale-cart-qty button {
        width: 2.25rem;
    }

    .sale-cart-qty button:hover,
    .sale-cart-remove:hover {
        background: rgb(39 39 42);
        color: rgb(250 250 250);
    }

    .sale-cart-qty span {
        display: grid;
        min-width: 2.5rem;
        place-items: center;
        border-right: 1px solid rgb(63 63 70);
        border-left: 1px solid rgb(63 63 70);
        padding: 0 0.75rem;
        font-weight: 700;
    }

    .sale-cart-remove {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
    }

    .sale-cart-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid rgb(39 39 42);
        background: rgb(9 9 11);
        padding: 1rem;
    }

    .sale-cart-total-label {
        color: rgb(212 212 216);
        font-size: 0.875rem;
        font-weight: 700;
    }

    .sale-cart-total-value {
        font-size: 1.25rem;
        font-weight: 800;
    }

    @media (max-width: 767px) {
        .sale-cart-header {
            display: none;
        }

        .sale-cart-row {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .sale-cart-right,
        .sale-cart-center {
            text-align: left;
        }

        .sale-cart-mobile-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
    }
</style>

<div class="sale-cart">
    @if ($cart === [])
        <div class="sale-cart-empty">
            <div class="sale-cart-empty-icon">
                <x-heroicon-o-shopping-bag style="width: 1.25rem; height: 1.25rem;" />
            </div>
            <p class="sale-cart-empty-title">Няма добавени продукти</p>
            <p class="sale-cart-empty-text">Добавените артикули ще се покажат тук.</p>
        </div>
    @else
        <div class="sale-cart-header">
            <span>Продукт</span>
            <span class="sale-cart-right">Цена</span>
            <span class="sale-cart-center">Количество</span>
            <span class="sale-cart-right">Общо</span>
            <span></span>
        </div>

        @foreach ($cart as $key => $item)
            <div class="sale-cart-row">
                <div>
                    <div class="sale-cart-product">{{ $item['name'] }}</div>
                    <div class="sale-cart-muted sale-cart-mobile-line">
                        <span>€{{ number_format($item['price'], 2) }} / бр.</span>
                    </div>
                </div>

                <div class="sale-cart-right sale-cart-muted">
                    €{{ number_format($item['price'], 2) }}
                </div>

                <div class="sale-cart-center sale-cart-mobile-line">
                    <span class="sale-cart-muted">Количество</span>
                    <div class="sale-cart-qty">
                        <button
                            type="button"
                            wire:click="decreaseCartItem('{{ $key }}')"
                            aria-label="Намали"
                        >
                            <x-heroicon-m-minus style="width: 1rem; height: 1rem;" />
                        </button>
                        <span>{{ $item['quantity'] }}</span>
                        <button
                            type="button"
                            wire:click="increaseCartItem('{{ $key }}')"
                            aria-label="Увеличи"
                        >
                            <x-heroicon-m-plus style="width: 1rem; height: 1rem;" />
                        </button>
                    </div>
                </div>

                <div class="sale-cart-right sale-cart-mobile-line">
                    <span class="sale-cart-muted">Общо</span>
                    <strong>€{{ number_format($item['total'], 2) }}</strong>
                </div>

                <div class="sale-cart-right">
                    <button
                        type="button"
                        wire:click="removeCartItem('{{ $key }}')"
                        class="sale-cart-remove"
                        aria-label="Премахни"
                    >
                        <x-heroicon-m-trash style="width: 1rem; height: 1rem;" />
                    </button>
                </div>
            </div>
        @endforeach

        <div class="sale-cart-total">
            <span class="sale-cart-total-label">Крайна сума</span>
            <span class="sale-cart-total-value">€{{ number_format($total, 2) }}</span>
        </div>
    @endif
</div>
