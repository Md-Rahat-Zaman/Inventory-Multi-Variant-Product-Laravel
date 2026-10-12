// alert('QUICK CREATE LOADED');
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('quickCreateModal');
    const form = document.getElementById('quickCreateForm');

    if (!modal || !form) return;

    const title = document.getElementById('quickCreateModalTitle');
    const subtitle = document.getElementById('quickCreateModalSubtitle');
    const method = document.getElementById('quickCreateMethod');
    const typeInput = document.getElementById('quickCreateType');
    const name = document.getElementById('quickCreateName');
    const description = document.getElementById('quickCreateDescription');
    const descriptionGroup = document.getElementById('quickCreateDescriptionGroup');
    const status = document.getElementById('quickCreateStatus');
    const saveText = document.getElementById('quickCreateSaveText');
    const saveBtn = document.getElementById('quickCreateSaveBtn');


    const config = {

        category: {
            name: 'Category',
            route: '/categories',
            select: 'category_id',
            response: 'category',
            description: true
        },

        unit: {
            name: 'Unit',
            route: '/units',
            select: 'unit_id',
            response: 'unit',
            description: false
        },

        brand: {
            name: 'Brand',
            route: '/brands',
            select: 'brand_id',
            response: 'brand',
            description: false
        },

        color: {
            name: 'Color',
            route: '/colors',
            select: 'color_id',
            response: 'color',
            description: false
        },

        size: {
            name: 'Size',
            route: '/sizes',
            select: 'size_id',
            response: 'size',
            description: false
        }

    };


    /*
    |--------------------------------------------------------------------------
    | Open Quick Create Modal
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.quick-create-btn').forEach(button => {

        button.addEventListener('click', function () {

            const mode = this.dataset.mode;
            alert(mode);
            const type = this.dataset.type;

            const item = config[type];

            if (!item) return;

            typeInput.value = type;


            /*
            |--------------------------------------------------------------------------
            | CREATE
            |--------------------------------------------------------------------------
            */

            if (mode === 'create') {

                form.action = item.route;

                method.value = 'POST';

                title.textContent =
                    'Add ' + item.name;

                subtitle.textContent =
                    'Create a new ' +
                    item.name.toLowerCase();

                saveText.textContent =
                    'Save ' + item.name;

                name.value = '';

                description.value = '';

                status.value = '1';


                if (item.description) {

                    descriptionGroup.style.display = '';

                } else {

                    descriptionGroup.style.display = 'none';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | EDIT
            |--------------------------------------------------------------------------
            */

            if (mode === 'edit') {

                let data = null;

                if (this.dataset.item) {

                    data = JSON.parse(
                        this.dataset.item
                    );

                } else if (this.dataset.category) {

                    data = JSON.parse(
                        this.dataset.category
                    );

                } else if (this.dataset.unit) {
                    data = JSON.parse(
                        this.dataset.unit
                    );

                } else if (this.dataset.brand) {

                    data = JSON.parse(
                        this.dataset.brand
                    );

                } else if (this.dataset.color) {

                    data = JSON.parse(
                        this.dataset.color
                    );

                } else if (this.dataset.size) {

                    data = JSON.parse(
                        this.dataset.size
                    );

                }

                if (!data) return;


                form.action =
                    item.route + '/' + data.id;

                method.value = 'PUT';

                title.textContent =
                    'Edit ' + item.name;

                subtitle.textContent =
                    'Update ' +
                    item.name.toLowerCase() +
                    ' information';

                saveText.textContent =
                    'Update ' + item.name;

                name.value =
                    data.name ?? '';

                description.value =
                    data.description ?? '';

                status.value =
                    data.status ?? '1';


                if (item.description) {

                    descriptionGroup.style.display = '';

                } else {

                    descriptionGroup.style.display = 'none';

                }

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | AJAX Submit
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const type = typeInput.value;
        const item = config[type];

        if (!item) return;


        const formData = new FormData(form);


        saveBtn.disabled = true;


        fetch(form.action, {

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),

                'Accept': 'application/json'

            },

            body: formData

        })

        .then(response => {

            return response.json().then(data => {

                if (!response.ok) {

                    throw data;

                }

                return data;

            });

        })

        .then(data => {

            if (!data.success) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Add / Update Select
            |--------------------------------------------------------------------------
            */

            const createdItem =
                data[item.response];


            if (createdItem) {

                const select = document.querySelector(
                    'select[name="' +
                    item.select +
                    '"]'
                );


                if (select) {

                    const existingOption =
                        select.querySelector(
                            `option[value="${createdItem.id}"]`
                        );


                    if (existingOption) {

                        existingOption.textContent =
                            createdItem.name;

                        existingOption.selected =
                            true;

                    } else {

                        const option = new Option(
                            createdItem.name,
                            createdItem.id,
                            true,
                            true
                        );

                        select.appendChild(option);

                    }


                    select.value =
                        createdItem.id;


                    select.dispatchEvent(
                        new Event('change', {
                            bubbles: true
                        })
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Close Modal
            |--------------------------------------------------------------------------
            */

            const modalInstance =
                bootstrap.Modal.getInstance(modal);


            if (modalInstance) {

                modalInstance.hide();

            }


            /*
            |--------------------------------------------------------------------------
            | Reset Form
            |--------------------------------------------------------------------------
            */

            form.reset();

            method.value = 'POST';

            typeInput.value = '';

            status.value = '1';

            descriptionGroup.style.display = '';


        })

        .catch(error => {

            console.error(
                'Quick create error:',
                error
            );

        })

        .finally(() => {

            saveBtn.disabled = false;

        });

    });

});