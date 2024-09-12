import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = {
        addLabel    : String,
        deleteLabel    : String,
    }

    connect() {
        // L'index, c'est le nombre d'éléments enfant
        this.index = this.element.childElementCount

        // Bouton ajouter
        const btn = document.createElement('button')
        btn.setAttribute('class', 'btn btn-sm btn-primary')
        btn.setAttribute('type', 'button')
        btn.innerHTML = this.addLabelValue || 'Ajouter un element'

        btn.addEventListener('click', this.addCollectionElement)
        this.element.childNodes.forEach(this.deleteCollectionElement)
        this.element.append(btn)
    }

    addCollectionElement = (e)=> {
        e.preventDefault()

        const element = document.createRange().createContextualFragment(
            this.element.dataset["prototype"].replaceAll('__name__', this.index)
        ).firstChild
        this.deleteCollectionElement(element)
        this.index++
        e.currentTarget.insertAdjacentElement('beforebegin', element)
    }

    /**
     *
     * @param {HTMLElement} item
     */
    deleteCollectionElement = (item) => {
        // Bouton ajouter
        const btn = document.createElement('button')
        btn.setAttribute('class', 'btn btn-sm btn-destructive')
        btn.setAttribute('type', 'button')
        btn.innerHTML = this.deleteLabelValue || 'Supprimer un element'

        item.append(btn)
        btn.addEventListener('click', e => {
            e.preventDefault()

            item.remove()
        })
    }
}
