=== DSFM-send-coupon  ===

Contributors: fmarzocca
Tags: coupon, contact form 7, email, attachment
Requires at least: 4.0.1
Tested up to: 4.1
Stable tag: 0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Invia un coupon su richiesta dell'utente

== Description ==
Il plugin contente l'inserimento di un tasto in qualunque pagina che permetterà all'utente di ricevere via email uno specifico coupon (allegato pdf), dopo aver inserito i propri dati.

I dati dell'utente saranno poi conservati in una tabella del DB di WordPress, a fini statistici.

Per l'esecuzione di questo plugin è necessario installare e attivare i seguenti plugin:

* Contact Form 7
* Easy FancyBox

= Shortcode =

Aggiungi questo shortcode:

<pre><code>
[richiedi-coupon oldprice="100€" offerprice="50€" cf7=4239 filename="coupons/item1.pdf" tasto="Richiedi il Coupon"]
</code></pre>

parametri accettati:

* `cf7` è l'id del form di Contact Form 7 per richiedere i dati all'utente;
* `tasto` è il testo da presentare sul bottone. Default = "Stampa il Coupon";
* `filename` contiene la path relativa e il nome del file da allegare. I file dei coupon sono nel folder wp-contents/uploads
* `titolo` è il titolo da presentare in testa al riquadro. Default="Scegli l'offerta";
* `testo` è il testo da visualizzare per l'offerta;
* `oldprice` vecchio prezzo (barrato);
* `offerprice` prezzo dell'offerta.  

__NOTA__

Nel contact form, nella sezione MAIL da inviare all'utente, aggiungere il seguente codice nel campo `File attachments`:

<pre><code>
[coupon]
</code></pre>

== Installation ==

1. Upload `DSFM-send-coupon` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Posso modificare lo stile del bottone? =
   Si, lo stile del bottone può essere modificato nel child-theme, inserendo la classe *coupon-button*.



== Changelog ==

= 0.7 =
* Allo shortcode è ora associato un div completo per l'offerta
* Abilitata funzionalità multipla su stessa pagina

= 0.5 =
* First working version