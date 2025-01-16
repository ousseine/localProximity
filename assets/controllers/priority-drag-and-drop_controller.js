import { Controller } from "@hotwired/stimulus";
import { Sortable } from "sortablejs"

export default class extends Controller {
    values = {
        url: String
    }

    connect() {
        const sortable = Sortable.create(this.element, {
            animation: 150,
            onEnd: (e) => {
                const ids = Array.from(this.element.children).map(tr => tr.dataset.id)
                this.updatePriority(ids)
            }
        })
    }

    updatePriority = (ids) => {
        fetch('/localProximity/survey/update-priority', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(ids)
        })
    }
}