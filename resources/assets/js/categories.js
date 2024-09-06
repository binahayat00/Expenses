import { Modal } from "bootstrap"

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))

    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            const categoryId = event.currentTarget.getAttribute('data-id')

            fetch(`/categories/${categoryId}`)
                .then(response => response.json())
                .then(response => openEditCategoryModal(editCategoryModal, response))

            // TODO: Fetch category info from controller & pass it to this function
        })
    })

    document.querySelector('.save-category-btn').addEventListener('click', function (event) {
        const categoryId = event.currentTarget.getAttribute('data-id')
        const csrfName = editCategoryModal._element.querySelector('input[name="csrf_name"]').value
        const csrfValue = editCategoryModal._element.querySelector('input[name="csrf_value"]').value
        const categoryName = editCategoryModal._element.querySelector('input[name="name"]').value
        
        fetch(`/categories/${categoryId}`, {
            method: 'POST',
            body: JSON.stringify({
                name: categoryName,
                csrf_name: csrfName,
                csrf_value: csrfValue,
            }),
            header: {
                'Content-Type' : 'application/json'
            }
        }).then(response => {
            console.log(response)
        })


    })
})

function openEditCategoryModal(modal, {id, name}) {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)

    modal.show()
}