import L from 'leaflet'
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';

export default class Map extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
            <div class="flex items-center justify-center mt-5">
                <div id="map" class="w-full h-80 sm:h-96" style="height: 300px;"></div>
            </div>`

        // Ajout du map
        const southWest = L.latLng(41.3337, -5.1406); // Sud-Ouest
        const northEast = L.latLng(51.124, 9.6625); // Nord-Est
        const bounds = L.latLngBounds(southWest, northEast);
        const options = {
            maxBounds: bounds,
            maxBoundsViscosity: 1.0
        }

        const map = L.map('map', options).setView([43.2965, 5.3698], 14)

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attributes: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            minZoom: 14,
            maxZoom: 19
        }).addTo(map);

        const markerIcon = L.icon({
            iconUrl: 'images/marker-icon.png',
            iconSize: [25, 41]
        })
        const provider = new OpenStreetMapProvider();
        const searchControl = new GeoSearchControl({
            notFoundMessage: 'Sorry, that address could not be found.',
            provider: provider,
            style: 'bar',
            marker: {
                icon: markerIcon,
                draggable: true
            }
        })

        map.addControl(searchControl)

        let marker;

        map.on('geosearch/showlocation', (result) => {
            marker = result.marker

            getLatLng(result.location.y, result.location.x)
        })

        function getMarker(lat, lng) {
            marker = L.marker([lat, lng], {
                icon: markerIcon,
                draggable: true,
                title: 'cliquer ici'
            }).addTo(map)

            getLatLng(lat, lng)

            // TODO :: Ajouter les infos dans le marker si on le clique et ne rien faire
        }

        map.on('click', e => {
            if (bounds.contains(e.latlng)) {
                let lat = e.latlng.lat
                let lng = e.latlng.lng

                if (marker) {
                    // Supprimer le marker
                    map.removeLayer(marker)

                    // Créer un nouveau marker
                    getMarker(lat, lng)
                } else {
                    // Créer un marker
                    getMarker(lat, lng)
                }
            }
        })

        // Supprimer le marker si on clique sur le bouton closed
        this.querySelector('form').querySelector('button').addEventListener('click', e => {
            if (marker) {
                map.removeLayer(marker)
            }
        })

        const getLatLng = (lat, lng) => {
            // On récupère les inputs
            const inputLat = document.querySelector('input[class="latitude"]')
            const inputLng = document.querySelector('input[class="longitude"]')

            // On ajoute les valeurs dans les inputs
            inputLat.value = lat
            inputLng.value = lng
        }
    }
}
