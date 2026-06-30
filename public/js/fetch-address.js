/**
 * Fetch City, State, and Area based on Pincode
 * API: http://www.postalpincode.in/api/pincode/{pincode}
 */

document.addEventListener('DOMContentLoaded', function () {
    const pincodeInput = document.getElementById('pincode');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const areaInput = document.getElementById('area');

    if (pincodeInput) {
        pincodeInput.addEventListener('blur', function () {
            const pincode = this.value.trim();

            if (pincode.length === 6 && /^\d+$/.test(pincode)) {
                // Show loading state
                const originalCityPlaceholder = cityInput ? cityInput.getAttribute('placeholder') : '';
                const originalStatePlaceholder = stateInput ? stateInput.getAttribute('placeholder') : '';
                const originalAreaPlaceholder = areaInput ? areaInput.getAttribute('placeholder') : '';

                if (cityInput) cityInput.setAttribute('placeholder', 'Fetching...');
                if (stateInput) stateInput.setAttribute('placeholder', 'Fetching...');
                if (areaInput && !areaInput.value) areaInput.setAttribute('placeholder', 'Fetching...');

                fetch(`/fetch-pincode/${pincode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.Status === 'Success') {
                            const postOffice = data.PostOffice[0]; // Take the first one

                            if (cityInput) {
                                cityInput.value = postOffice.Taluk;
                                cityInput.setAttribute('placeholder', originalCityPlaceholder || '');
                            }

                            if (stateInput) {
                                stateInput.value = postOffice.State;
                                stateInput.setAttribute('placeholder', originalStatePlaceholder || '');
                            }

                            // Optional: Fill Area with the Post Office Name if empty
                            if (areaInput && !areaInput.value) {
                                areaInput.value = postOffice.Name;
                                areaInput.setAttribute('placeholder', originalAreaPlaceholder || '');
                            }
                        } else {
                            console.warn('Pincode not found or API error');
                            // Reset placeholders
                            if (cityInput) cityInput.setAttribute('placeholder', originalCityPlaceholder || '');
                            if (stateInput) stateInput.setAttribute('placeholder', originalStatePlaceholder || '');
                            if (areaInput) areaInput.setAttribute('placeholder', originalAreaPlaceholder || '');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching pincode data:', error);
                        // Reset placeholders
                        if (cityInput) cityInput.setAttribute('placeholder', originalCityPlaceholder || '');
                        if (stateInput) stateInput.setAttribute('placeholder', originalStatePlaceholder || '');
                        if (areaInput) areaInput.setAttribute('placeholder', originalAreaPlaceholder || '');
                    });
            }
        });
    }
});
