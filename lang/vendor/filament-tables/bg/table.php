<?php

return [

    'column_manager' => [

        'heading' => 'Колони',

        'actions' => [

            'apply' => [
                'label' => 'Приложи колоните',
            ],

            'reset' => [
                'label' => 'Нулирай',
            ],

        ],

    ],

    'columns' => [

        'actions' => [
            'label' => 'Действие|Действия',
        ],

        'select' => [

            'loading_message' => 'Зареждане...',

            'no_options_message' => 'Няма налични опции.',

            'no_search_results_message' => 'Няма съвпадения за търсенето ви.',

            'placeholder' => 'Избери опция',

            'searching_message' => 'Търсене...',

            'search_prompt' => 'Започни да пишеш за търсене...',

        ],

        'text' => [

            'actions' => [
                'collapse_list' => 'Покажи с :count по-малко',
                'expand_list' => 'Покажи с :count повече',
            ],

            'more_list_items' => 'и още :count',

        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'Избери/махни избора на всички записи на тази страница.',
        ],

        'bulk_select_record' => [
            'label' => 'Избери/махни избора на запис :key за групови действия.',
        ],

        'bulk_select_group' => [
            'label' => 'Избери/махни избора на група :title за групови действия.',
        ],

        'search' => [
            'label' => 'Търсене',
            'placeholder' => 'Търсене',
            'indicator' => 'Търсене',
        ],

    ],

    'summary' => [

        'heading' => 'Обобщение',

        'subheadings' => [
            'all' => 'Всички :label',
            'group' => 'Обобщение за :group',
            'page' => 'Тази страница',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'Средно',
            ],

            'count' => [
                'label' => 'Брой',
            ],

            'sum' => [
                'label' => 'Сума',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'Приключи пренареждането',
        ],

        'enable_reordering' => [
            'label' => 'Пренареди записите',
        ],

        'filter' => [
            'label' => 'Филтрирай',
        ],

        'group' => [
            'label' => 'Групирай',
        ],

        'open_bulk_actions' => [
            'label' => 'Групови действия',
        ],

        'column_manager' => [
            'label' => 'Колони',
        ],

    ],

    'empty' => [

        'heading' => 'Няма :model',

        'description' => 'Създай :model, за да започнеш.',

    ],

    'filters' => [

        'actions' => [

            'apply' => [
                'label' => 'Приложи',
            ],

            'remove' => [
                'label' => 'Премахни филтъра',
            ],

            'remove_all' => [
                'label' => 'Премахни всички',
                'tooltip' => 'Премахни всички филтри',
            ],

            'reset' => [
                'label' => 'Нулирай',
            ],

        ],

        'heading' => 'Филтри',

        'indicator' => 'Активни филтри',

        'multi_select' => [
            'placeholder' => 'Всички',
        ],

        'select' => [

            'placeholder' => 'Всички',

            'relationship' => [
                'empty_option_label' => 'Няма',
            ],

        ],

        'trashed' => [

            'label' => 'Изтрити записи',

            'only_trashed' => 'Само изтрити записи',

            'with_trashed' => 'С изтрити записи',

            'without_trashed' => 'Без изтрити записи',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'Групирай по',
            ],

            'direction' => [

                'label' => 'Посока на групиране',

                'options' => [
                    'asc' => 'Възходящо',
                    'desc' => 'Низходящо',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'Премествай записите с мишката.',

    'selection_indicator' => [

        'selected_count' => '1 запис избран|:count записа избрани',

        'actions' => [

            'select_all' => [
                'label' => 'Избери всички :count',
            ],

            'deselect_all' => [
                'label' => 'Премахни избора на всички',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'Сортирай по',
            ],

            'direction' => [

                'label' => 'Посока на сортиране',

                'options' => [
                    'asc' => 'Възходящо',
                    'desc' => 'Низходящо',
                ],

            ],

        ],

    ],

    'default_model_label' => 'запис',

];
