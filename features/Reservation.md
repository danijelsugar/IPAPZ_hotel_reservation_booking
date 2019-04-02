Reservation handles everything reservation related from booking part to accepting or declining reservations, listing all
pending reservation, listing accepted and declined reservations. Users can see only their reservations, admin and 
employee can see all reservations.

ReservationController functions

    -index() - on home page shows all reservations for each day in calendar
    -booking() - user choose their date from, date to and number of people. Based on that they get rooms that are 
    available or room are soon available
    -editReservation - user can change the reservation date
    -createPdf() - when user change reservation date if payment method is invoice pdf will be updated with new cost
    -roomReservations - shows for each room dates when room is not available. Each room has it own calendar that shows 
    its reservations
    -userReservations - show reservations for logged in user
    -reservations() - shows all received reservations with sorting grid. Admin can sort received reservations
    by email, date, room name
    -acceptedReservations() - lists all accepted reservations
    -declinedReservations() - lists all declined reservations
    -acceptReservation() - admin can accept reservation
    -cancelReservation() - admin can cancel reservation
    -declineReservation() - admin can decline reservation
    -downloadPdf() - admin can download pdf or reservations which payment method is invoice