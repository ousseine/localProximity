import { Controller} from "@hotwired/stimulus";

export default class extends Controller {

    connect() {

        this.element.addEventListener('click', () => this.expend())
    }

    expend() {
        let toggleSidebar = false

        if (toggleSidebar) {
            toggleSidebar = false
        } else {
            toggleSidebar = true
        }

        console.log(toggleSidebar, 'yes')

        // const sidebar = document.getElementById('sidebar')
        // const adminContent = document.querySelector('.admin-content')
        //
        // sidebar.classList.toggle('active')
        // toggleSidebar = !toggleSidebar

        // if (sidebar.style.width === '16rem') {
        //     sidebar.style.width = '4.5rem';
        //     adminContent.style.marginLeft = '4.5rem';
        // } else {
        //     sidebar.style.width = '16rem';
        //     adminContent.style.marginLeft = '16rem';
        // }

        // const labels = sidebar.querySelectorAll('span');
        // labels.forEach(label => label.classList.toggle('opacity-0'));
    }
}