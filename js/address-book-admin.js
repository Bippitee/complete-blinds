//prevent unnecessary loading of the Google Maps API
const tableExists = !!document.getElementById(
  "address_book_table_with_autocomplete"
);

function initializeAutocomplete() {
  // Get the address fields
  let addressField = document.getElementById("address_book_address");
  let cityField = document.getElementById("address_book_city");
  let stateField = document.getElementById("address_book_state");
  let postcodeField = document.getElementById("address_book_postcode");
  let countryField = document.getElementById("address_book_country");
  let phoneField = document.getElementById("address_book_phone");
  let websiteField = document.getElementById("address_book_website");
  let placeIdField = document.getElementById("address_book_placeId");

  let titleField = document.getElementById("title");

  const options = {
    componentRestrictions: { country: "au" },
    placeholder: "",
    fields: ["address_components", "geometry", "name", "place_id"],
    types: ["establishment"],
  };
  // Create the autocomplete object
  let autocomplete = new google.maps.places.Autocomplete(titleField, options);

  // Listen for the place changed event
  autocomplete.addListener("place_changed", function () {
    let place = autocomplete.getPlace();
    if (!place.geometry) {
      // Clear the fields if no valid place is selected
      addressField.value = "";
      cityField.value = "";
      stateField.value = "";
      postcodeField.value = "";
      countryField.value = "";
      return;
    }

    // Set the values for each field
    addressField.value = "";
    cityField.value = "";
    stateField.value = "";
    postcodeField.value = "";
    countryField.value = "";

    // Fill in the address components
    place.address_components.forEach(function (component) {
      let componentType = component.types[0];

      if (componentType === "street_number") {
        addressField.value = component.long_name + " " + addressField.value;
      } else if (componentType === "route") {
        addressField.value += component.short_name;
      } else if (
        componentType === "locality" ||
        componentType === "postal_town"
      ) {
        cityField.value = component.long_name;
      } else if (componentType === "administrative_area_level_1") {
        stateField.value = component.short_name;
      } else if (componentType === "postal_code") {
        postcodeField.value = component.long_name;
      } else if (componentType === "country") {
        countryField.value = component.long_name;
      }
    });

    //add the place name to the title field
    titleField.value = place.name;
    placeIdField.value = place.place_id;

    let placeId = place.place_id;

    let service = new google.maps.places.PlacesService(
      document.createElement("div")
    );
    // Use the service to get place details
    service.getDetails(
      {
        placeId: placeId,
      },
      function (result, status) {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
          // Access the place details
          //   console.log(result);

          // Example: Get the phone number
          var phoneNumber = result.formatted_phone_number;
          phoneField.value = phoneNumber;

          // Example: Get the website
          var website = result.website;
          websiteField.value = website;
        }
      }
    );
  });
}

// Load the Google Maps JavaScript API with the callback parameter
function loadGoogleMapsAPI() {
  let apiKey = api.key; // comes from thw wp_localize_script function in complete_blinds.php
  let script = document.createElement("script");
  script.src =
    "https://maps.googleapis.com/maps/api/js?key=" +
    apiKey +
    "&libraries=places&callback=initializeAutocomplete";
  document.body.appendChild(script);
}

// Call the function to load the Google Maps JavaScript API
if (tableExists) {
  loadGoogleMapsAPI();
}
