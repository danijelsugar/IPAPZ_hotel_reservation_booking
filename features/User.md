Users differ by their roles(admin,employee,user). Regular user can register and they get role user, they only can make
reservation, see their reservations, change reservation details, leave review when reservation passes.
Employees can see all pending, accepted and declined reservations, he can accept or decline received reservations. 
Admin have all other rights, he can add new room, edit room, hide or activate room, add categories and 
subcategories, edit categories and subcategories, hide or activate them, enable or disable payment methods, add new 
employees, edit employees.

UserController functions:

    -Admin can add new employee - newEmployee()
    -Admin can delete employee - deleteEmployee()
    -Admin can edit employee details - editEmployee()
    -Users needs to be registered to reserv room - register()
    -Users can login to their account -login()
    -logout() 
