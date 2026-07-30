const form = document.getElementById("orderForm");

form.addEventListener("submit", function(event){

    event.preventDefault();

    clearErrors();

    let valid = true;

    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let studentId = document.getElementById("studentId").value.trim();
    let department = document.getElementById("department").value;
    let quantity = Number(document.getElementById("quantity").value);
    let instructions = document.getElementById("instructions").value;

    if(name===""){
        document.getElementById("nameError").innerText="Name is required";
        valid=false;
    }

    let emailPattern=/^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

    if(!email.match(emailPattern)){
        document.getElementById("emailError").innerText="Enter a valid email";
        valid=false;
    }

    if(phone===""){
        document.getElementById("phoneError").innerText="Phone is required";
        valid=false;
    }

    if(studentId===""){
        document.getElementById("studentError").innerText="Student ID is required";
        valid=false;
    }

    let gender=document.querySelector('input[name="gender"]:checked');

    if(!gender){
        document.getElementById("genderError").innerText="Select gender";
        valid=false;
    }

    if(department===""){
        document.getElementById("departmentError").innerText="Select department";
        valid=false;
    }

    let foods=document.querySelectorAll(".food:checked");

    if(foods.length===0){
        document.getElementById("foodError").innerText="Select at least one food item";
        valid=false;
    }

    if(quantity<=0 || isNaN(quantity)){
        document.getElementById("quantityError").innerText="Quantity must be greater than 0";
        valid=false;
    }

    if(!valid){
        return;
    }

    let totalPrice=0;
    let selectedItems="";

    foods.forEach(function(food){

        let price=Number(food.dataset.price);

        totalPrice+=price;

        selectedItems+=food.value+" - $"+price+"<br>";

    });

    let total=totalPrice*quantity;

    document.getElementById("result").innerHTML=`

    <h2>Order placed successfully!</h2>

    <p><strong>Customer Name:</strong> ${name}</p>

    <p><strong>Student ID:</strong> ${studentId}</p>

    <p><strong>Department:</strong> ${department}</p>

    <p><strong>Selected Items:</strong><br>${selectedItems}</p>

    <p><strong>Quantity:</strong> ${quantity}</p>

    <p><strong>Total Bill:</strong> $${total}</p>

    <p><strong>Special Instructions:</strong> ${instructions || "None"}</p>

    `;

});

function clearErrors(){

    let errors=document.querySelectorAll(".error");

    errors.forEach(function(error){

        error.innerText="";

    });

}