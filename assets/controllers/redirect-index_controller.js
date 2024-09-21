import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        this.element.addEventListener('click', e => {
            e.preventDefault()

            setTimeout(function () {
                window.location.href = "/localProximity/answer"
            }, 300)
        })
    }
}