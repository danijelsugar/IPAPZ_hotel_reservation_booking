Rooms have category, subcategory, description, image, status which indicates if room is visible or not and cost

RoomController functions

    -rooms() - when booking user choose for how many people they want to reserv room based on that only rooms that match 
    will be displayed. After that there are few checks if room is available in given date period. If there are no available
    rooms in given perion few soon available rooms will be displayed with calendar so user can se when they will be 
    availabe
    -allRooms() - list of all rooms displayed to admin
    -createRoom() - admin can add new room
    -editRoom() - admin can edit existing room
    -disableRoom() - admin can hide room so user wont see it and cant reserv it
    -enableRoom() - admin can enable room so it is visible to users again