/*Pasar pedidos de En espera a cancelados*/
UPDATE wpjv_posts
SET post_status = 'wc-cancelled'
WHERE post_status = 'wc-on-hold'
AND post_type = 'shop_order';



/*borar tickets cancelados pendientes*/

DELETE FROM wpjv_posts
WHERE post_status IN ('lty_ticket_pending', 'wc-cancelled');
