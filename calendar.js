document.addEventListener('DOMContentLoaded', function() {
    const roomPicker = document.getElementById('roomPicker');
    const startDateInput = document.querySelector('input[name="startDate"]');
    const endDateInput = document.querySelector('input[name="endDate"]');
    
    async function getDisabledDates(roomId) {
        const response = await fetch(`booking.php?getBookedDates=1&roomId=${roomId}`);
        const bookedDates = await response.json();
        return bookedDates;
    }
    
    async function initializeDatepickers(roomId) {
        const bookedDates = await getDisabledDates(roomId);
        
        const commonConfig = {
            minDate: "2025-01-01",
            maxDate: "2025-01-31",
            disable: bookedDates.map(booking => ({
                from: booking.start_date,
                to: booking.end_date
            }))
        };

        // Start date picker
        flatpickr(startDateInput, {
            ...commonConfig,
            onChange: function(selectedDates) {
                endPicker.set('minDate', selectedDates[0]);
            }
        });

        // End date picker
        const endPicker = flatpickr(endDateInput, {
            ...commonConfig,
            minDate: startDateInput.value || "2025-01-01"
        });
    }
    
    roomPicker.addEventListener('change', function() {
        initializeDatepickers(this.value);
    });
    
    initializeDatepickers(roomPicker.value);
});