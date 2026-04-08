document.addEventListener("DOMContentLoaded", function() {
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("uploadButton");
    if (btn) {
        btn.onclick = function() {
            modal.style.display = "block";
        }
    }
});

function closeModal() {
    document.getElementById("myModal").style.display = "none";
}

window.onclick = function(event) {
    var modal = document.getElementById("myModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}