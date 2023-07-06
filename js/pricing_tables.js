jQuery(document).ready(function($) {
    // Handler for blind type dropdown change
    $('#blind-type').change(function() {
        updatePricingTable();
    });

    // Handler for group dropdown change
    $('#group_number').change(function() {
        updatePricingTable();
    });

    

    // Function to update the pricing data table
    function updatePricingTable() {
        var blindType = $('#blind-type').val();
        var group = $('#group_number').val();
        
        // Send AJAX request to fetch pricing data
        $.ajax({
            url: myAjax.ajaxurl, // AJAX URL
            type: 'POST',
            data: {
                action: 'get_pricing_data',
                blind_type: blindType,
                group_number: group,
                security: myAjax.blind_pricing_nonce
            },
            success: function (response) {
                if (response.data === 'Select a blind and group') {
                    // Display the message in a suitable location
                    $('#pricing-table-container').html(response.data);
                } else if (response.data === 'No pricing data found') {
                    // Display the message in a suitable location
                    let html = '<p>No pricing data found for the selected blind and group.</p><div class="file-upload"><label for="pricing-data-file"><div class="icon-container"><svg aria-hidden="true" class="svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg><p class="paragraph-text"><span class="font-semibold">Click to upload CSV file</span> or drag and drop</p></div><input id="pricing-data-file"name="pricing-data-file" type="file"  accept=".csv" />    </label></div>';
                    $('#pricing-table-container').html(html);
                    $('#pricing-data-file').on('change', handleCSVFileChange);
                        //handler for file upload input
                        $('.file-upload label').on('dragenter', function(e) {
                            this.classList.add('dragover');
                        })
                        .on('dragleave dragend drop dragexit', function(e) {
                            this.classList.remove('dragover');

                        });
                } else {
                    // Update the pricing table with the received HTML
                    $('#pricing-table-container').html(response.data);

                    $('#pricing-table-container #pricing-table-body td').on('click', handleCellClick);
                    $('#pricing-table-container #delete-pricing-data').on('click', handleDeletePricingDataConfirm);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error occurred during AJAX request:', error);
                $('#pricing-table-container').html('An error occurred during the AJAX request.');
            }
        });
    }

    // Initial update of pricing table
    updatePricingTable();


    let expandedCell = null;

    // Handler for cell click
    function handleCellClick(e) {
      // Collapse the currently expanded cell, if any
    if (expandedCell !== null) {
        if (expandedCell.find(e.target).length > 0) {
            return;
        } else {
        collapseCell(expandedCell);
        }
    }
        //get the cell value
        let cellValue = $(this).html() || '0';
        
  //create a form to edit the cell value
        let popup = $('<div><div class="popup"><input type="text" name="price" value="' + parseInt(cellValue).toFixed(0) + '" /><button><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></button></div></div>');
        
        //check if aria-expanded is true
        if ($(this).attr('aria-expanded') === 'true') {
            //if it is true, then we are already editing this cell
            return;
        }
        //set the selected attribute on $this
        $(this).attr('aria-expanded', 'true');
        $(this).html(cellValue + popup.html());

        //add event listener for enter key
        $(this).find('input').on('keyup', function(e) {
            if (e.keyCode === 13) {
                $(this).parent().find('button').trigger('click');
            }
        });

        expandedCell = $(this);
        }

    // Function to collapse a cell
function collapseCell(cell) {
    // Remove the popup form
    cell.find('.popup').remove();

    // Reset the cell content
    if(cell.html() === '0') {
        cell.html('');
    } else {
    cell.html(parseFloat(cell.html().replace(/<br>.*/, '')).toFixed(2));
    }

    // Set the aria-expanded attribute to false
    cell.attr('aria-expanded', 'false');
}

//handlers for closing the open inputs if any.
$(document).on('click', function(e) {
                
        // Check if the click was outside the table
    if (e.target.closest('tbody') === null) {
        // Collapse the currently expanded cell, if any
        if (expandedCell !== null) {
            collapseCell(expandedCell);
        }
    }
});

    // Handler for escape key press
    $(document).on('keyup', function(e) {
        if (expandedCell !== null) {
        if (e.keyCode === 27) {
            // Collapse the currently expanded cell, if any
                collapseCell(expandedCell);
            }
        }
    });


    // Handler for save button click
    $(document).on('click', '.popup button', function() {
        //get the new cell value
        let newValue = $(this).parent().find('input').val();
        let cellId = parseInt($(this).parent().parent().attr('id')) || 'new';
        const isNewEntry = isNaN(cellId);
        let price = parseFloat(newValue);

        //get the cell
        let cell = $(this).parent().parent();
        let rowIdx = cell.parent().index();
        let colIdx = cell.index();

        //get the associated row and column headers
        let blind_drop = cell.parent().parent().find('th').eq(rowIdx).html();
        let blind_width = cell.parent().parent().parent().find('thead th').eq(colIdx).html();
        let blind_type = $('#blind-type').val();
        let group_number = $('#group_number').val();

        console.log(blind_drop, blind_width, blind_type, group_number, price);
        //if isNewEntry is true, then we are inserting a new price
        if (isNewEntry) {
            cell.attr('id', 'replace-me');

            insertNewPrice(blind_type, group_number, blind_width, blind_drop, price);
        } else {
            updatePrice(cellId, price);
        }

        //set the cell value
        cell.html(parseFloat(newValue).toFixed(2));
        //close the popup
        cell.attr('aria-expanded', 'false');
        // cell.find('.popup').remove();
    });


    function insertNewPrice(blind_type, group_number, blind_width, blind_drop, price) {
        let width = parseFloat(blind_width);
        let drop = parseFloat(blind_drop);
        let priceInt = parseFloat(price);
        let group_noInt = parseInt(group_number);

        $.ajax({
            url: myAjax.ajaxurl, // AJAX URL
            type: 'POST',
            data: {
                action: 'new_pricing_data',
                blind_type: blind_type,
                group_number: group_noInt,
                blind_width: width,
                blind_drop: drop,
                price: priceInt,
                security: myAjax.blind_pricing_nonce
            },
            success: function (response) {
                if (response.data === 'Select a blind and group') {
                    // Display the message in a suitable location
                    $('#pricing-table-container').html(response.data);
                } else {
                    // Update the pricing table with the received HTML
                    $('#replace-me').attr('id', response.data);

                    $('#pricing-table-container #pricing-table-body td').on('click', handleCellClick);
                    
                }
            },
            error: function(xhr, status, error) {
                console.log('Error occurred during AJAX request:', error);
                $('#pricing-table-container').html('An error occurred during the AJAX request.');
            }
        });
    }

    function updatePrice(id, price) {
        let idInt = parseInt(id);
        let priceFloat = parseFloat(price);
        $.ajax({
            url: myAjax.ajaxurl, // AJAX URL
            type: 'POST',
            data: {
                action: 'update_pricing_data',
                id: idInt,
                price: priceFloat,
                security: myAjax.blind_pricing_nonce
            },
            success: function (response) {
                if (response.data === 'Select a blind and group') {
                    // Display the message in a suitable location
                    $('#pricing-table-container').html(response.data);
                } else {
                    // Update the pricing table with the received HTML
                    $('#id').html(response.data);

                    $('#pricing-table-container #pricing-table-body td').on('click', handleCellClick);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error occurred during AJAX request:', error);
                $('#pricing-table-container').html('An error occurred during the AJAX request.');
            }
        });
    }

   function handleCSVFileChange(event) {
        var file = event.target.files[0];
        var reader = new FileReader();
      
        // Event listener for file reading completion
        reader.onload = function(e) {
          var contents = e.target.result;
          // Parse the CSV data
          var parsedData = parseCSV(contents);
          // Handle the parsed data
          handleParsedData(parsedData);
        };
      
        // Start reading the file
        reader.readAsText(file);
      }
      
     // Function to parse CSV data
     function parseCSV(csvData) {
        let lines = csvData.replace('\r', '').split('\n');
        let col_names = lines[0].split(',').slice(1); // Exclude the first empty value
        let row_names = [];
        let data = [];
      
        for (let i = 1; i < lines.length; i++) {
          let line = lines[i].split(',');
          row_names.push(line[0]);
          data.push(line.slice(1));
        }
      
        return {
          col_names: col_names,
          row_names: row_names,
          data: data
        };
      }
      
      let csvEntries = [];
      function handleParsedData(parsedData) {
        //clear cvsEntries
        csvEntries = [];
        let blind_type = $('#blind-type').val();
        let group_number = parseInt($('#group_number').val());
        let { col_names, row_names, data } = parsedData;
        

      
        // Iterate over each value in the data array
        for (let rowIndex = 0; rowIndex < data.length; rowIndex++) {
          let rowData = data[rowIndex];
      
          for (let colIndex = 0; colIndex < rowData.length; colIndex++) {
            let blindWidth = parseInt(col_names[colIndex]);
            let blindDrop = parseInt(row_names[rowIndex]);
            let price = parseInt(rowData[colIndex]);
      
            // Create an entry object with the required attributes
            let entry = {
            blind_type: blind_type,
            group_number: group_number,
            blind_width: blindWidth,
            blind_drop: blindDrop,
            price: price
            };
      
            csvEntries.push(entry);
          }
        }

       //transform entries into a <table> and display it a grid with width on the x-axis and drop on the y-axis
         let table = '<table class="widefat striped">';
            table += '<thead><tr><th></th>';
            for (let i = 0; i < col_names.length; i++) {
                table += '<th>' + col_names[i] + '</th>';
            }
            table += '</tr></thead>';
            table += '<tbody>';
            for (let i = 0; i < row_names.length -1; i++) {
                table += '<tr><th>' + row_names[i] + '</th>';
                for (let j = 0; j < data[i].length; j++) {
                    table += '<td>' + data[i][j] + '</td>';
                }
                table += '</tr>';
            }
            table += '</tbody>';
            table += '</table>';

            //add save button
            table += '<button id="save-csv" class="button button-primary" style="margin-top: 1rem;">Save</button>';

            
            $('#pricing-table-container').html(table);

            //add event listener to save button
            $('#save-csv').on('click', handleSave);
      }

       function handleSave() {
        if(csvEntries.length === 0) {
            console.log('No entries to save');
            return;
        }
        //json encode csvEntries
        let entries = JSON.stringify(csvEntries);
        console.log(entries);
        $.ajax({
            url: myAjax.ajaxurl, // AJAX URL
            type: 'POST',
            data: {
              action: 'batch_insert_pricing_data', 
              security: myAjax.blind_pricing_nonce,
              entries: entries,
            },
            success: function(response) {
              // Handle the success response from the server
              console.log('Batch write successful:', response);
              $('#pricing-table-container').html('<div class="success-message"><p>Batch write successful!</p><span> Refreshing pricing table...</span></div>');
              //slight delay to allow the user to see the success message, then update the pricing table
                setTimeout(function() {
                    updatePricingTable();
                }, 2000);
                


            },
            error: function(xhr, status, error) {
                console.log(xhr);
              // Handle the error response from the server
              console.log('Error occurred during batch AJAX request:', error);
            },
            complete: function() {
                //clear csvEntries
                csvEntries = [];
            }

          });
        }


        function handleDeletePricingDataConfirm() {
            let blind_type = $('#blind-type').val();
            let group_number = parseInt($('#group_number').val());
            let confirm = window.confirm('Are you sure you want to delete all pricing data for ' + blind_type + ' group ' + group_number + '?');
            if(confirm) {
                deletePricingData(blind_type, group_number);
            }
        }

        function deletePricingData(blind_type, group_number) {
            $.ajax({
                url: myAjax.ajaxurl, // AJAX URL
                type: 'POST',
                data: {
                  action: 'bulk_delete_pricing_data', 
                  security: myAjax.blind_pricing_nonce,
                  blind_type: blind_type,
                  group_number: group_number
                },
                success: function(response) {
                  // Handle the success response from the server
                  console.log('Delete successful:', response);
                  $('#pricing-table-container').html('Delete successful');
                  updatePricingTable();
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                  // Handle the error response from the server
                  console.log('Error occurred during delete AJAX request:', error);
                }
              });
        }
});

// $.ajax({
//     url: myAjax.ajaxurl, // AJAX URL
//     type: 'POST',
//     data: {
//       action: 'batch_insert_pricing_data', 
//       entries: entries,
//       security: myAjax.blind_pricing_nonce
//     },
//     success: function(response) {
//       // Handle the success response from the server
//       console.log('Batch write successful:', response);
//     },
//     error: function(xhr, status, error) {
//       // Handle the error response from the server
//       console.log('Error occurred during batch AJAX request:', error);
//     }
//   });