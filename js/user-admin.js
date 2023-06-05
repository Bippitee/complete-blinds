jQuery(document).ready(function ($) {
  // Function to update the options of the second dropdown based on the selected value in the first dropdown
  function updateAddressBookProfile(selectedType) {
    let addressBookProfile = $("#address-book-profile");
    // Clear the current options
    addressBookProfile.empty();

    // Add default option
    addressBookProfile.append(
      $("<option>", {
        value: "",
        text: "Select an Address Book Entry",
      })
    );

    if (selectedType) {
      // Fetch the options for the selected contact type and append them to the second dropdown
      $.ajax({
        url:
          "../wp-json/complete-blinds/v1/get-address-book-entries/" +
          selectedType,
        dataType: "json",
        success: function (response) {
          if (response.length > 0) {
            $.each(response, function (index, entry) {
              addressBookProfile.append(
                $("<option>", {
                  value: entry.id,
                  text: entry.label, // entry.label is the title and city of the address book entry
                  selected: entry.selected,
                })
              );
            });
            addressBookProfile.prop("disabled", false);
          } else {
            addressBookProfile.empty();
            addressBookProfile.append(
              $("<option>", {
                value: "",
                text: "No Options Found for this Customer Type",
              })
            );
            addressBookProfile.prop("disabled", true);
          }
        },
      }); // end ajax call
    } else {
      addressBookProfile.empty();
      addressBookProfile.append(
        $("<option>", {
          value: "",
          text: "Choose a Customer Type First",
        })
      );
      addressBookProfile.prop("disabled", true);
    }
  }

  // On page load
  let selectedType = $("#contact-type-selector").val();
  updateAddressBookProfile(selectedType);

  // Change the options of the second dropdown based on the selected value in the first dropdown
  $("#contact-type-selector").on("change", function () {
    let selectedType = $(this).val();
    updateAddressBookProfile(selectedType);
  });
});
