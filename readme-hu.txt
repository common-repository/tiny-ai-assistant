== Tiny AI Assistant ==
Contributors: monsz
Donate link: https://tiny-ai-assistant-hu.aichatbot.hu/
Tags: ChatGPT, OpenAI, AI, GPT-4, Ai content writer, copywriting, Content Writing Assistant, Content Writer, TinyMCE
Requires PHP: 5.6
Requires at least: 5.0
Tested up to: 6.2.2
Stable tag: 1.1
License: GPLv2 or later

== Leírás ==

Felturbóztuk a TinyMCE szövegszerkesztőt, hogy még könnyebben és gyorsabban állíthass elő szövegeket. 
A mi kis programunk beépül a szerkesztő felületekbe (az összes szövegszerkesztőbe a weblap szerkesztő felületén), a parancsot a szövegdobozba beírod, egy kattintás és már meg is jelent a kívánt szöveg. 
Ennél egyszerűbb, gyorsabb nem is lehet.

A gyakran használt parancsokat elmentheted, ezáltal a jövőben egyetlen kattintásra lesz csak szükség!

Használata:
    Telepítés után add meg az API kulcsot amit az openai.com-on tudsz előállítani.
    Állítsd be a parancsokat (ráérsz később is). Az ingyenes verzióban 3 parancs menthető el.


Prémium verzió:
A prémium verzióban több lehetőséget kapsz:
    - Beállíthatod mennyire legyen kreatív a szöveg
    - Bármennyi parancsot létrehozhatsz


Hamarosan:
    - Többféle AI szolgáltatót használhatsz majd
    - A parancsok sorrendjét is meghatározhatod


Támogatott pluginek
	- Elementor
	- ACF 



== Telepítés ==

1. Töltsük fel a bővítmény mappáját a Wordpress /wp-content/plugins/ mappájába.
2. A vezérlőpulton a Telepített bővítmények oldalon kapcsoljuk be a kiegészítőt.
3. Nyissuk meg a vezérlőpulton a Beállítások->Tiny AI Assistant beállítások beállításai aloldalt.
4. Adjuk meg az OpenAI.com-nál magunknak generált API kulcsot.
5. A Tiny AI Assistant beállítások oldalon Új parancsokat adhatunk hozzá a használható parancsokhoz a következő lépésekkel az "Új parancs hozzáadása" szekcióban:
	a) Megadjuk a parancs nevét (pl: "Vers írás")
	b) Megadjuk a chatGPT-nek küldendő parancsot (pl.: "Írj verset 8 sorban ebben a témában:")
	c) Megnyomjuk a "+" gombot. Ekkor a legfelül látható "Aktív parancsok" beviteli mezőbe bekerült az új parancs,
	d) Megnyomjuk a Tiny AI Assistant beállítások oldal alján található "Mentés" gombot, hogy a beállítások mentésre kerüljenek
6. A már felvett parancsok a "Parancsok eltávolítása" szekcióban eltávolíthatóak igény szerint.
7. Válasz kreativitás beállítása: 0 és 1 között állítható, a 0 a legkevésbé kreatív (legkiszámíthatóbb) eredmény generálását eredményezi, az 1 pedig a legkreatívabb (legkiszámíthatatlanabb) eredményt adja.
8. Amennyiben előfizetett a szolgáltatásra, a "Licence kulcs (Prémium előfizetéshez)" szekcióban adja meg az előfizetésnél kapott licensz kulcsát.
9. Fontos, hogy minden, a Tiny AI Assistant beállítások oldalon történt módosítás rögzítéséhez nyomja meg az oldal alján található "Mentés" gombot.



== Használat ==

Miután a Telepítésnél leírtak szerint beállította az esetleges licensz kulcsot, illetve a használni kívánt parancsokat, 
a wordpress felületén bármely helyen megjelenő TinyMCE szerkesztő eszköztárban megjelenik a Tiny AI Assistant parancsait tartalmazó lenyíló lista, illetve mellette a visszavonást kiváltó gomb.
FONTOS!
A Tiny AI Assistant lenyíló listáját és visszavonás gombját a TinyMCE szerkesztő eszköztárának MÁSODIK sorában találja, ami alapértelmezetten becsukott állapotban van, így a Tiny AI Assistant eszközeinek
eléréséhez be kell kapcsolnia az eszköztár második sorának megjelenését az erre szolgáló gombbal!

A teendője mindössze annyi, hogy az adott szerkesztő eszköztárhoz tartozó szövegszerkesztő felületen megadja a parancshoz tartozó információt, pl. Ha a "Vers írás" parancsot használja, ott egy témát vár logikusan a rendszer,
tehát megadja a szövegszerkesztő részben például azt, hogy "Hupikék Törpikék", kiválasztja a lenyíló listában a "Vers írás" opciót, ezután kis várakozás után a "Hupikék Törpikék" szöveg helyett megjelenik a generált vers.

Amennyiben a generált tartalom nem megfelelő, a lenyíló lista mellett található visszavonás gombra kattintva visszakapja a generálás előtti szövegszerkesztő tartalmat.

FONTOS!
A token felhasználásból nem kerül visszavonásra ilyenkor a nem felhasznált tartalom, tehát ami parancsot egyszer kiadott a rendszernek és arra kapott választ, az ezzel felhasznált token mindenképpen elhasználásra került.
Emellett kiemelendő, hogy mind a parancs kiadása, mind a visszakapott tartalom használ tokent, 
tehát ha egy hosszabb szöveget fordít a rendszerrel például, ott egy A4-es oldal szöveg fordítása 2 A4-es oldalnyi szöveget jelentő token felhasználással számoljon.

== Külső szolgáltatás hívások ==
A plugin a saját checker API szolgáltatásunkat (https://construct.pdk.hu/tinyAIEx-checker/checker_api.php) használja arra, hogy a plugin használata során lekérje a használandó OpenAI licensz kulcsot. 
A checker API a felhasználó által a plugin beállításai között megadott licensz kulcs alapján adja vissza az OpenAI kulcsot, amennyiben az előfizetéshez még tartozik felhasználható token keret.

A checker API-val kapcsolatos adatvédelmi nyilatkozat: 
Az API használata során semmilyen személyes adat nem kerül átadásra, kizárólag a Tiny AI Assistant licensz kulcsa, illetve az ahhoz tartozó előfizetői csomag neve, felhasznált, illetve felhasználható tokenszám kerül átadásra. 

== Gyakran Ismételt Kérdések ==

Kérdés: Hogy tudok OpenAI kulcsot szerezni?
Válasz: Látogass el az openai.com oldalra, hozz létre saját fiókot, miután ott bejelentkeztél, navigálj el az API szekcióba, ott tudsz saját API kulcsot generálni.
		
Kérdés: Nem működik a bővítmény, hibaüzenetet kapok. Mit tegyek?
Válasz:	Előfordulhat, hogy az OpenAI rendszere is túlterhelődik, érdemes ilyenkor kicsit várni.
		Amennyiben rövid időn belül nem áll helyre a szolgáltatás, kérjük jelezze nekünk a tiny-ai-assistant@aichatbot.hu email címen, vagy a bővítmény honlapján a kapcsolat űrlapon.

