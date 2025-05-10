// admin.js
const modal = document.getElementById("bookingModal");
const closeModal = document.querySelector(".close-modal");
const viewButtons = document.querySelectorAll(".view-details");

viewButtons.forEach(button => {
    button.addEventListener("click", function () {
        const eventType = this.getAttribute("data-event-type");
        const bookingId = this.getAttribute("data-booking-id");
        const customerName = this.getAttribute("data-name");
        const customerEmail = this.getAttribute("data-email");
        const customercontact = this.getAttribute("data-contact");
        const bookingDate = this.getAttribute("data-booking-date");
        const eventDate = this.getAttribute("data-event-date");
        const status = this.getAttribute("data-status");
        const total = this.getAttribute("data-total");

        let item1Label, item2Label, item3Label, item4Label;
        switch (eventType) {
            case 'wedding':
                item1Label = 'Flower Decoration';
                item2Label = 'Entrance Design';
                item3Label = 'Cardboard Theme';
                item4Label = 'Venue';
                break;
            case 'art':
                item1Label = 'Room Lighting';
                item2Label = 'Prop';
                item3Label = 'Cardboard Theme';
                item4Label = 'Venue';
                break;
            case 'conference':
                item1Label = 'Lighting';
                item2Label = 'Table Decoration';
                item3Label = 'Cardboard Theme';
                item4Label = 'Venue';
                break;
        }

        document.getElementById("modalTitle").textContent = `${eventType.charAt(0).toUpperCase() + eventType.slice(1)} Booking #${bookingId}`;

        document.getElementById("modalContent").innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Customer Information</h3>
                        <p><strong>Name:</strong> ${customerName}</p>
                        <p><strong>Email:</strong> ${customerEmail}</p>
                        <p><strong>contact:</strong> ${customercontact}</p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Booking Information</h3>
                        <p><strong>Booking Date:</strong> ${bookingDate}</p>
                        <p><strong>Event Date:</strong> ${eventDate}</p>
                        <p><strong>Status:</strong> <span class="status status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Selected Items</h3>
                        <p><strong>${item1Label}:</strong> ${this.getAttribute("data-item1")}</p>
                        <p><strong>${item2Label}:</strong> ${this.getAttribute("data-item2")}</p>
                        <p><strong>${item3Label}:</strong> ${this.getAttribute("data-item3")}</p>
                        <p><strong>${item4Label}:</strong> ${this.getAttribute("data-item4")}</p>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <p><strong>Total Price:</strong> Rs. ${total}</p>
                    </div>
                `;

        modal.style.display = "block";
    });
});

closeModal.addEventListener("click", function () {
    modal.style.display = "none";
});

window.addEventListener("click", function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
});

document.querySelectorAll('.description').forEach(desc => {
            desc.addEventListener('click', function() {
                const fullText = this.getAttribute('title');
                if (fullText) {
                    alert('Customer Feedback:\n\n' + fullText);
                }
            });
        });