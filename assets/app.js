import './bootstrap.js'
import 'leaflet/dist/leaflet.min.css'
import 'leaflet-geosearch/dist/geosearch.min.css'
import './styles/app.css'
import Map from './js/Map.js'

customElements.define('address-map', Map)


// Check line answer
const inputsCheck = document.querySelectorAll('input[name^=checkbox_delete]')
const form_delete = document.querySelector('form[name="form_delete"]');
const select_ids = []

inputsCheck.forEach(checkbox => {

    checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
            select_ids.push(checkbox.value)
        } else {
            // Retirer l'ID si la case est décochée
            const index = select_ids.indexOf(checkbox.value)

            if (index > -1 ) {
                select_ids.splice(index, 1) // On retire l'id du tableau
            }
        }

        if (select_ids.length > 0) {
            form_delete.classList.remove('hidden')
        } else {
            form_delete.classList.add('hidden')
        }

        form_delete.querySelector('#delete_group').value = select_ids.join(',')
    })
})
