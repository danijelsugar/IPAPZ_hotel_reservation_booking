# IPAPZ_hotel_reservation_booking
Hotel reservation booking


1.Nužne funkcionalnosti:
1.1.Javni dio:
  * prikazuje se kalendar s slobodnim sobama po danu
  * kreiranje rezervacije sobe, odabire se datumi od do, vrsta sobe, unosi se email
1.2.Privatni:
  * hotel moze promjeniti status rezervacije, moze ju odbit, prihvatit ...
  * u kalendaru se vide odbijene rezervaciije
  * hotel moze unositi vrste sobe i konkretne sobe 
  * na odobernu rezervacjiu se dodjeljuje konkretna soba
  * admin moze unijeti vrsu sobe, status rezervacije, sobu i hotelskog djelatnika
  
2.Poželjne
* hotelski djelatnik moze mijenjati sve podatke rezervacije
* admin moze mijenjati i brisati vrsu sobe, status rezervacije, sobu i hotelskog djelatnika
* na javnom dijelu moze se pretrazivati opisi soba
* pregled soba 10 po stranici učitanje preko ajaxa
* svakoj sobi je moguce dodjeliti vise slika od kojih je jedna glavna

3.Opcionalne
* svakoj sobi dodjeljuje se qr code o podatcima sobe
* za svaku rezervaciju je moguce kreirati pdf dokument
* export svih podataka o svim rezervacijama u exel, sheet se zove rezervacije
* svakoj sobi se moze dodjeliti pokemon
* rest api za dani broj sobe dobiva se json sa svim podatcima sobe
* po potvrđenoj rezervaciji salje se mail korisniku sa kreiranim pdf-om
