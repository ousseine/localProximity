import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['options', 'submitButton', 'areaFields']

    connect() {
        const form = this.element
        const submitButton = this.submitButtonTarget

        // Si on a des champs de texte (ou autre), le bouton doit être affiché
        if (this.areaFieldsTargets.length > 0) {
            submitButton.style.display = 'block'; // Garder le bouton pour texte et textarea
        } else {
            submitButton.style.display = 'none'; // Cacher le bouton pour les choix uniques/multiples
        }

        const checkAllFieldsFilled = (choiceFields) => {
            // Vérifie que les champs texte ou textarea sont remplis
            const areFieldsFilled = Array.from(this.areaFieldsTargets).every(field => {
                return field.value.trim() !== ''; // Vérifie si les champs ne sont pas vides
            });

            // Vérifie que les champs radio/checkbox requis ont une sélection
            const requiredFields = Array.from(choiceFields).filter(field => field.required) // Champs radio/checkbox requis
            const areChoiceFieldsFilled = requiredFields.every(function(field) {
                const group = document.querySelectorAll(`input[name="${field.name}"]`);
                return Array.from(group).some(radio => radio.checked); // Vérifie si un bouton est coché dans le groupe
            });

            return areFieldsFilled && areChoiceFieldsFilled;
        }

        const items = [];
        this.optionsTargets.forEach(choiceField => {
            const inputs = choiceField.querySelectorAll('input')
            inputs.forEach((fieldInput) => { items.push(fieldInput) })
        })

        // Écoute les changements sur les champs à choix unique ou multiple
        items.forEach(item => {
            item.addEventListener('change', () => {
                if (checkAllFieldsFilled(items)) {
                    form.submit(); // Soumettre le formulaire seulement quand tout est rempli
                }
            })
        })

        // Écoute les changements sur les champs texte pour réactiver le bouton si nécessaire
        this.areaFieldsTargets.forEach(field => {
            field.addEventListener('input', () => submitButton.disabled = !checkAllFieldsFilled())
        });
    }
}
