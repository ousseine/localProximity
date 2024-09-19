import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static ids = []
    form = document.querySelector('form[name="form_delete"]')

    connect() {
        this.element.addEventListener('change', this.handleDelete)

        // Cibler le formulaire de suppression
        if (this.form) {
            this.form.addEventListener('submit', () => {
                this.constructor.ids = [] // Nettoyer ids après soumission
            })
        }
    }

    handleDelete = () => {
        if (this.element.checked) {
            if (!this.constructor.ids.includes(this.element.value)) this.constructor.ids.push(this.element.value)
        } else {
            // Retirer l'ID si la case est décochée
            const index = this.constructor.ids.indexOf(this.element.value)

            if (index > -1 ) {
                this.constructor.ids.splice(index, 1) // On retire l'id du tableau
            }
        }

        if (this.constructor.ids.length > 0) {
            this.form.classList.remove('hidden')
            console.log(this.constructor.ids, 'checked')
        } else {
            this.form.classList.add('hidden')
            console.log(this.constructor.ids, 'unchecked')
            this.constructor.ids = []
        }

        this.form.querySelector('input[id="delete_ids"]').value = this.constructor.ids.join(',')
    }
}