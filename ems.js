// ems.js
const popups = {
    "contact": {
        link: document.getElementById("contact-link"),
        overlay: document.getElementById("popup-overlay-contact")
    },
    "about": {
        link: document.getElementById("about-link"),
        overlay: document.getElementById("popup-overlay-about")
    }
};

const showPopup = (overlayId) => {
    const popupOverlay = document.getElementById(overlayId);
    popupOverlay.style.display = "block";
};

const hidePopup = (overlayId) => {
    const popupOverlay = document.getElementById(overlayId);
    popupOverlay.style.display = "none";
};

for (let key in popups) {
    const { link, overlay } = popups[key];
    
    link.addEventListener("click", (e) => {
        e.preventDefault();
        showPopup(overlay.id);
    });

    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            hidePopup(overlay.id);
        }
    });
}

document.querySelectorAll(".close-popup").forEach(button => {
    button.addEventListener("click", () => {
        Object.values(popups).forEach(({ overlay }) => {
            hidePopup(overlay.id);
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.getElementById('dashboard');
    const openBtn = document.getElementById('open-dashboard');
    const closeBtn = document.getElementById('close-dashboard');

    if (openBtn && closeBtn && dashboard) {
        openBtn.addEventListener('click', () => {
            dashboard.style.width = '250px';
            dashboard.style.transition = '0.5s';
        });
        
        closeBtn.addEventListener('click', () => {
            dashboard.style.width = '0';
            dashboard.style.transition = '0.5s';
        });
    } else {
        console.error("One or more dashboard elements are missing.");
    }
});

const selectedItems = { flower: null, entrance: null, cardboard: null, venue: null };

function updateSelectionDisplay() {
    const container = document.getElementById('selected-items');
    let html = '<ul>';
    let total = 0;
    for (const [category, item] of Object.entries(selectedItems)) {
        if (item) {
            html += `<li>${category.charAt(0).toUpperCase() + category.slice(1)}: ${item.description} - Rs. ${item.price.toFixed(2)}</li>`;
            total += item.price;
        }
    }
    if (total > 0) {
        html += `<li style="font-weight:bold;">Total: Rs. ${total.toFixed(2)}</li>`;
    }
    html += '</ul>';
    container.innerHTML = html || '<p>No items selected yet</p>';

    document.getElementById('selected-flower').value = selectedItems.flower?.id || '';
    document.getElementById('selected-entrance').value = selectedItems.entrance?.id || '';
    document.getElementById('selected-cardboard').value = selectedItems.cardboard?.id || '';
    document.getElementById('selected-venue').value = selectedItems.venue?.id || '';
}

document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        selectedItems[this.name] = { id: this.value, description: this.dataset.description, price: parseFloat(this.dataset.price) };
        updateSelectionDisplay();
    });
});

document.querySelector('form').addEventListener('submit', function(e) {
    const eventDate = document.getElementById('event_date').value;
    if (!eventDate) {
        e.preventDefault();
        alert('Please select an event date');
        return false;
    }
    document.getElementById('selected-event-date').value = eventDate;
    return true;
});

