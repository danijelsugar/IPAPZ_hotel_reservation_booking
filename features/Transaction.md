Holds all features regarding payment

PaymentTransactionController

    -payPalShow() - show paypal page to user when he choose paypal method
    -payment() - check and validate everything regarding payment with paypal
    -gateway() - creates braintree gateway
    -invoicePayment() - chech and validates everything regarding company payment
    -createPdf() - when user decides to pay with invoice pdf is created holding reservation 
    details(room,amount,date period)