//get url parameters
const urlParams = new URLSearchParams(window.location.search);
//get the value of the url parameter 'page'
const page = urlParams.get("page");

//if the page is complete_blinds_blinds
if (page === "complete_blinds_blinds") {
  document.addEventListener("DOMContentLoaded", function () {
    let typeIndex = 0;
    let addTypeButton = document.getElementById(
      "complete_blinds_blinds_add_type"
    );

    //ADD TYPE
    addTypeButton.addEventListener("click", function () {
      let table = document
        .getElementById("complete_blinds_blinds_types_table")
        .getElementsByTagName("tbody")[0];
      let newRow = table.insertRow();

      //Label
      let labelCell = newRow.insertCell();
      let labelInput = document.createElement("input");
      labelInput.type = "text";
      labelInput.name =
        "complete_blinds_blinds_types[" + typeIndex + "][label]";

      labelInput.addEventListener("keyup", (e) => handleLabelKeypress(e));
      labelCell.appendChild(labelInput);

      //Value
      // let valueCell = newRow.insertCell();
      let valueInput = document.createElement("input");
      valueInput.type = "hidden";
      valueInput.name =
        "complete_blinds_blinds_types[" + typeIndex + "][value]";

      labelCell.appendChild(valueInput);

      //Checkboxes
      let hasSubtypesCell = newRow.insertCell();
      let hasSubtypesInput = document.createElement("input");
      hasSubtypesInput.type = "checkbox";
      hasSubtypesInput.name =
        "complete_blinds_blinds_types[" + typeIndex + "][has_subtypes]";
      hasSubtypesCell.appendChild(hasSubtypesInput);

      typeIndex++;
    });

    //SUBTYPES CHECKBOX EVENT LISTENER
    document
      .querySelectorAll(
        "input[name^='complete_blinds_blinds_types'][name$='[has_subtypes]']"
      )
      .forEach(function (input) {
        let subtypesTable = input
          .closest("tr")
          .nextElementSibling.querySelector(".complete-blinds-subtypes-table");

        input.addEventListener("change", function () {
          subtypesTable.style.display = this.checked ? "table" : "none";
          //get the name of the input that was changed
          let name = this.name;
          //find the first value in the name between [];
          let value = name.match(/\[(.*?)\]/)[1];
          //set the data-value attribute of the table to the value of the input that was changed
          subtypesTable.dataset.value = value;
        });
      });

    //ADD SUBTYPE ROW
    document
      .querySelectorAll(".complete-blinds-add-subtype")
      .forEach(function (button) {
        button.addEventListener("click", function () {
          let subtypesTable = this.closest("td").querySelector(
            ".complete-blinds-subtypes-table tbody"
          );
          let subtypeIndex = subtypesTable.children.length;
          //get dataset.value from parent table i.e. level above current table
          let value = this.closest("td").querySelector(
            ".complete-blinds-subtypes-table"
          ).dataset.value;

          let newRow = document.createElement("tr");

          //Label
          let labelCell = document.createElement("td");
          let labelInput = document.createElement("input");
          labelInput.type = "text";
          labelInput.dataset.entry = "label";
          labelInput.name =
            "complete_blinds_blinds_types[" +
            value +
            "][subtypes][" +
            subtypeIndex +
            "][label]";

          labelInput.addEventListener("keyup", (e) => handleLabelKeypress(e));

          labelCell.appendChild(labelInput);

          //Value
          // let valueCell = document.createElement("td");
          let valueInput = document.createElement("input");
          valueInput.type = "hidden";
          valueInput.dataset.entry = "value";
          valueInput.name =
            "complete_blinds_blinds_types[" +
            value +
            "][subtypes][" +
            subtypeIndex +
            "][value]";
          labelCell.appendChild(valueInput);

          newRow.appendChild(labelCell);

          subtypesTable.appendChild(newRow);

          let parentTable = this.closest("td").querySelector("table");
          parentTable.setAttribute("aria-hidden", "false");
        });
      });

    // keypress to update label and value
    let labelInputs = document.querySelectorAll('input[name $= "[label]"]');

    labelInputs.forEach((input) => {
      input.addEventListener("keyup", (e) => handleLabelKeypress(e));
    });

    function handleLabelKeypress(e) {
      //get th input that was changed
      let input = e.target;
      //get the value of the input
      let value = input.value;
      input.value = titleFormat(value);
      //convert the value to lowercase, remove training spaces, and replace other spaces with hyphens
      let slugvalue = value.toLowerCase().trim().replace(/\s/g, "-");
      //find all inputs in the same table row
      let inputs = input.closest("tr").querySelectorAll("input");
      //loop through all inputs
      inputs.forEach((input) => {
        if (!input.name.includes("[subtypes]")) {
          //replace the text in the first set of brackets in the name of the input to the value of the input that was changed
          input.name = input.name.replace(/\[.*?\]/, `[${slugvalue}]`);
        } else {
          //find values between [] in the name of the input
          let values = input.name.match(/\[(.*?)\]/g);
          //replace second to last value with the value of the input that was changed
          input.name = input.name.replace(
            values[values.length - 2],
            `[${slugvalue}]`
          );
        }
      });

      //the second input in the row is the value input
      let valueInput = inputs[1];
      valueInput.value = slugvalue;
    }
  });
}

function titleFormat(title) {
  const lowercaseWords = [
    "a",
    "an",
    "the",
    "and",
    "but",
    "or",
    "for",
    "nor",
    "on",
    "at",
    "to",
    "from",
    "by",
    "over",
  ];

  const words = title.toLowerCase().split(" ");

  for (let i = 0; i < words.length; i++) {
    const word = words[i];
    if (lowercaseWords.includes(word) && i !== 0) {
      words[i] = word.toLowerCase();
    } else {
      words[i] = word.charAt(0).toUpperCase() + word.slice(1);
    }
  }

  return words.join(" ");
}
