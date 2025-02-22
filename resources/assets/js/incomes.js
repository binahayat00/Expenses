import { Modal } from "bootstrap"
import { get, post, del } from "./ajax"
import DataTable from "datatables.net"

import "../css/incomes.scss"

window.addEventListener('DOMContentLoaded', function () {
    const newIncomeModal = new Modal(document.getElementById('newIncomeModal'))
    const editIncomeModal = new Modal(document.getElementById('editIncomeModal'))

    const table = new DataTable('#incomesTable', {
        serverSide: true,
        ajax: '/incomes/load',
        orderMulti: false,
        rowCallback: (row, data) => {
            if (!data.wasReviewed) {
                row.classList.add('fw-bold')
            }
            return row;
        },
        columns: [
            { data: "source" },
            {
                data: 'amount',
                render: data => {
                    const amount = new Intl.NumberFormat(
                        'en-US',
                        {
                            style: 'currency',
                            currency: 'USD',
                            currencySign: 'accounting'
                        }
                    ).format(data)

                    return `<span class="${data > 0 ? 'text-success' : ''}">${amount}</span>`
                }
            },
            { data: "date" },
            {
                sortable: false,
                data: row => `
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <i class="bi bi-gear fs-4" role="button" data-bs-toggle="dropdown"></i>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item edit-income-btn" href="#" data-id="${row.id}">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item delete-income-btn" href="#" data-id="${row.id}">
                                        <i class="bi bi-trash3-fill"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                `
            }
        ],


    });

    document.querySelector('#incomesTable').addEventListener('click', function (event) {
        const editBtn = event.target.closest('.edit-income-btn')
        const deleteBtn = event.target.closest('.delete-income-btn')

        if (editBtn) {
            const incomeId = editBtn.getAttribute('data-id')

            get(`/incomes/${incomeId}`)
                .then(response => response.json())
                .then(response => openEditIncomeModal(editIncomeModal, response))
        } else if (deleteBtn) {
            const incomeId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this income?')) {
                del(`/incomes/${incomeId}`).then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    });

    document.querySelector('.create-income-btn').addEventListener('click', function (event) {
            post(`/incomes`, getIncomeFormData(newIncomeModal), newIncomeModal._element)
                .then(response => {
                    if (response.ok) {
                        table.draw()
    
                        newIncomeModal.hide()
                    }
                })
    });

    document.querySelector('.save-income-btn').addEventListener('click', function (event) {
        const incomeId = event.currentTarget.getAttribute('data-id')

        post(`/incomes/${incomeId}`, getIncomeFormData(editIncomeModal), editIncomeModal._element)
            .then(response => {
                if (response.ok) {
                    table.draw()
                    editIncomeModal.hide()
                }
            })
    });

});

function getIncomeFormData(modal) {
    let data = {}
    const fields = [
        ...modal._element.getElementsByTagName('input'),
        ...modal._element.getElementsByTagName('select')
    ]

    fields.forEach(select => {
        Object.keys(select).forEach(key => {
            data[select[key].name] = select[key].value
        })
    })
    return data
}

function openEditIncomeModal(modal, { id, ...data }) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${name}"]`)

        nameInput.value = data[name]
    }

    modal._element.querySelector('.save-income-btn').setAttribute('data-id', id)

    modal.show()

}