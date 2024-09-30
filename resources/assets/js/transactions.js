import { Modal } from "bootstrap"
import { get, post, del } from "./ajax"
import DataTable from "datatables.net"

window.addEventListener('DOMContentLoaded', function(){
    const newTransactionModal = new Modal(document.getElementById('newTransactionModal'))
    const editTransactionModal = new Modal(document.getElementById('editTransactionModal'))

    const table = new DataTable('#transactionsTable', {
        serverSide: true,
        ajax: '/transactions/load',
        orderMulti: false,
        columns: [
            {data: "description"},
            {
                data: row => new Intl.NumberFormat(
                    'en-US',
                    {
                        style: 'currency',
                        currency: 'USD',
                        currencySign: 'accounting'
                    }
                ).format(row.amount)
            },
            {data: 'category', sortable: false},
            {data: "date"},
            {
            }
        ]
    })
})