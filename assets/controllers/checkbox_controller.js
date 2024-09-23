import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static selectedRows = []
    static targets = [ "deleteForm", "rowCheckboxAll", "rowCheckbox", "rows", "rowNumber" ]
    static values = {
        rowChecked: String
    }

    connect() {
        this.rowCheckboxTargets.forEach(rowCheckbox => rowCheckbox.addEventListener('change', this.changeCheckbox))
        this.rowCheckboxAllTarget.addEventListener('change', this.changeAllCheckbox)
    }

    // Gestionnaire pour chaque checkbox individuelle
    changeCheckbox = () => {
        this.updateSelectedRows()
        this.updateSelectAllState()
    }

    // Fonction pour la gestion du checkbox "Select All"
    changeAllCheckbox = () => {
        if (this.rowCheckboxAllTarget.checked) {
            this.rowCheckboxTargets.forEach(row => row.checked = true)
        } else {
            this.rowCheckboxTargets.forEach(row => row.checked = false)
        }

        this.updateSelectedRows()
    }

    // Fonction pour mettre à jour le tableau des éléments sélectionnés
    updateSelectedRows = () => {
        const rowsCheckbox = this.rowCheckboxTargets.filter(checkbox => checkbox.checked)
        this.constructor.selectedRows = Array.from(rowsCheckbox).map(checkbox => parseInt(checkbox.value))

        this.rowsTarget.value = this.constructor.selectedRows.join(',')

        this.toggleDeleteForm()

        console.log(this.rowsTarget)
    }

    // Fonction pour vérifier si "Select All" doit être coché ou décoché
    updateSelectAllState = () => {
        const rowCheckboxLength = this.rowCheckboxTargets.length
        const totalRowsCheckedLength = this.rowCheckboxTargets.filter(checkbox => checkbox.checked).length

        this.rowCheckboxAllTarget.checked = (rowCheckboxLength === totalRowsCheckedLength)
    }

    // Fonction pour afficher ou cacher le formulaire de suppression
    toggleDeleteForm = () => {
        if (this.constructor.selectedRows.length > 0) {
            this.deleteFormTarget.classList.remove('hidden')
            this.rowNumberTarget.classList.remove('sr-only')
            this.selectedValue()
            this.rowNumberTarget.innerHTML = this.checkedValue
        } else {
            this.deleteFormTarget.classList.add('hidden')
            this.rowNumberTarget.classList.add('sr-only')
        }
    }

    // Fonction pour afficher le nombre total de lignes sélectionné
    selectedValue = () => {
        const selectedLine = this.rowCheckboxTargets.filter(checkbox => checkbox.checked).length

        if (this.rowCheckboxTargets.length === selectedLine) {
            this.checkedValue = "Toutes sélections"
        } else {
            const ligneText = selectedLine > 1 ? 'sélections' : 'sélection'
            this.checkedValue = `${selectedLine} ${ligneText}`
        }
    }
}
