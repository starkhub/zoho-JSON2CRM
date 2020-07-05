# zoho-JSON2CRM
Fetch contacts inside a JSON file and inject the result inside Zoho CRM

# Résumé
La liste des utilisateurs d'une application web est disponible dans un fichier JSON accessible depuis une URL.

Nous souhaitions synchroniser les utilisateurs de cette liste avec le CRM Zoho, que ce soit les Leads ou les contacts.

# Solution
1 - Nous récupérons dans un premier temps nos users dans notre fichier JSON, nous le préparons en éclatant les infos de chaque user dans des champs spécifiques :

- Nom
- Prénom
- Email
etc.

2 - Nous générons une clé unique via ces infos, la **u_key (clé unique de l'enregistrement côté JSON) : 

id,nom,prenom,email,telephone,datecreation, etc.

> Quand on récupère un enregistrement dans la liste JSON nous créons une clé unique avec ses infos, la **u_key, quand on récupère l'enregistrement dans Zoho nous faisons de même avec ses infos en créant la zu_key. En comparant les deux on peut voir si des infos ont changé...

3 - On définit si l'enregistrement est un Lead (prospect) ou un contact (client)

3.1 - Si c'est un Lead on vérifie s'il existe déjà : 
- Si c'est le cas on check si ses infos ont changé via la fonction getRecords() en comparant la **u_key et la **z_ukey
- Si besoin on met à jour le lead avec updateRecords(), sinon on continue...
- Si le lead n'existe pas, on le créé et on continue...
3.2 - Si c'est un contact on vérifie si c'est une conversion (Lead > Contact, un prospect qui signe un contrat et devient client) :
- Grâce à l'email de l'enregistrement on demande à Zoho s'il existe déjà dans notre base avec la fonction checkConvert()
- Si elle retourne "NOTALEAD" on créé un nouveau contact... (3.3)
- Si c'est un LEAD on procède à sa transformation LEAD vers CONTACT (Prospect vers client) avec updateLeadToContact()
3.3 - Pour la création d'un nouveau contact, on vérifie tout d'abord s'il existe déjà dans notre base Zoho :
- Si tel est le cas on utilise de nouveau getRecords() en comparant les **u_key et **zu_key
- Si besoin on met à jour le contact avec updateRecords()
- Sinon on créé le nouveau contact

Via le SDK PHP Zoho :

https://github.com/zoho/zcrm-php-sdk

https://www.zoho.com/crm/developer/docs/php-sdk/record-samples.html?src=convert_record

Installation :

https://www.zoho.com/crm/developer/docs/php-sdk/install-sdk.html

