<template>
    <div>
      <div id="map" style="height: 400px;"></div>
    </div>
</template>
  
  <script>
  import 'leaflet/dist/leaflet.css';
  import L from 'leaflet';
  import axios from 'axios';
  import Swal from 'sweetalert2';

  // Define custom marker icons
    const availableIcon = L.icon({
    iconUrl: '/markers/bluemarker.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    });

    const unavailableIcon = L.icon({
    iconUrl: '/markers/redmarker.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    });


  export default {
    data() {
      return {
        map: null,
        userSpotInfo:null,
        fetchingTimer: null,
      };
    },
    methods: {
    fetchLocations() {
    const userId = this.$page.props.auth.user.id;
      axios.get('/api/parkingspot/get')
        .then((response) => {
          const locations = response.data;
          locations.forEach(async (location) => {
            const { id, latitude, longitude, availability , user_id} = location;
            const markerIcon = availability === 1 ? unavailableIcon : availableIcon;
            const marker = L.marker([latitude, longitude], { icon: markerIcon }).addTo(this.map);
            if(availability === 1){
              marker.on('click', () => {
                Swal.fire({
                  icon: 'error',
                  title: 'Not Availabe',
                  text: 'Spot is reserved.',
                });
              });
            } else {
              marker.on('click', async () => {
                  const locInfo = await this.getLocationInfo(longitude, latitude);
                  const loadingMessage = 'Loading directions...';

                  Swal.fire({
                      icon: 'question',
                      text: 'Address Details:' + locInfo.display_name,
                      title: 'Get directions to this spot?',
                      showCancelButton: true,
                      confirmButtonText: 'Yes',
                      cancelButtonText: 'No',
                      showLoaderOnConfirm: true, // Show loader while processing
                      preConfirm: () => {
                          return new Promise((resolve) => {
                              // User wants to get directions, use Geolocation API to get user's location
                              if (navigator.geolocation) {
                                  navigator.geolocation.getCurrentPosition(
                                      (position) => {
                                          const { latitude: userLat, longitude: userLng } = position.coords;
                                          // Open a new window/tab with a link for directions
                                          window.open(`https://www.google.com/maps/dir/${userLat},${userLng}/${latitude},${longitude}`);
                                          resolve();
                                      },
                                      (error) => {
                                          console.error('Error getting user location:', error);
                                          // Handle error or provide a default location for directions
                                          resolve();
                                      }
                                  );
                              } else {
                                  console.error('Geolocation is not supported by this browser.');
                                  // Handle lack of geolocation support
                                  resolve();
                              }
                          });
                      },
                      allowOutsideClick: () => !Swal.isLoading(), // Prevent dismissing when loading
                  });
              });
            }

          });
        })
        .catch((error) => {
          console.error('Error fetching location data:', error);
        });
    },
    async getLocationInfo(long, lat) {
      try {
        const response = await axios.get(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${long}&format=json`);
        return response.data;
      } catch (error) {
        console.error('Error fetching location:', error);
        throw error; // Re-throw the error to handle it elsewhere if needed
      }
    },


    startFethching() {
      this.fetchLocations();
      // Set up a timer to call fetchLocations every 5 seconds
      this.fetchingTimer = setInterval(() => {
        this.fetchLocations();
      }, 5000); // 5 seconds interval
    },

  },
    mounted() {
      this.map = L.map('map');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map); 
      
      // Use the Geolocation API to get the user's current location
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
          const { latitude, longitude } = position.coords;
          // Set up a red dot marker for the user's current location
          const userMarker = L.marker([latitude, longitude], { icon: L.divIcon({ className: 'user-marker' }) })
          .addTo(this.map)
          .bindPopup('This is your location')
          .openPopup();
          this.map.setView([latitude, longitude], 13);
          this.startFethching(); 
        }, (error) => {
          console.error('Error getting user location:', error);
          // If geolocation fails, you can provide a default location here or display an error message.
        });
      } else {
        console.error('Geolocation is not supported by this browser.');
      }
    },
  };
  </script>
  
  <style>
  #map {
    width: 100%;
  }

  .user-marker {
    background-color: red;
    border-radius: 50%;
    height: 12px;
    width: 12px;
    margin-left: -6px;
    margin-top: -6px;
  }

  .cancel-button {
  background-color: #ff0000; 
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 10px 20px; 
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease; 
}

.cancel-button:hover {
  background-color: #cc0000; 
}
  </style>
  