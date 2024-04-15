const faqs = [
    {
        title: "Hvordan tjekker jeg en opstilling?",
        text: `
			<ol style="list-style: inside;">
				<li>Opret en bruger</li>
				<li>Tryk på "holdrunder" i menuen</li>
				<li>Opret holdrunde</li>
				<li>Tilføj alle holdene</li>
				<li>Tilføj spillerne på holdene</li>
			</ol>
			Hvis der er fejl eller mangler i opstillingen, markeres disse med forskellige farver på spillerne
		`,
    },
    {
        title: "Der vises en valideringsfejl selv om holdet er korrekt?",
        text: "Hvis du mener, at der er en fejl i valideringen af holdkampen, så send en mail til info@nembadminton.dk, med delingslinket som findes via export funktionen øverest. Husk at uddybe fejlen.",
    },
    {
        title: "Der mangler en spiller i min klub?",
        text: `nembadminton.dk henter data fra badmintonplayer.dk. Hvis spilleren ikke findes på den seneste måneds rangliste, så findes spilleren heller ikke på nembadminton.dk.
		Hvis spilleren findes på ranglisten, men stadig ikke findes på nembadminton.dk, så send en mail til info@nembadminton.dk med oplysninger om klubben og spilleren.`,
    },
    {
        title: "Jeg har et forslag til forbedringer eller en ny feature?",
        text: "Send meget gerne forslag til forbedringer til info@nembadminton.dk eller brug det røde spørgsmåltegn i højre hjørne af siden",
    },
    {
        title: "Der opstår en fejl, så jeg ikke kan komme videre?",
        text: `Send en mail til info@nembadminton.dk eller brug det røde spørgsmåltegn i højre hjørne af siden`
    },
    {
        title: "Hvad gør jeg, hvis holdene skal sættes efter forskellige ranglister?",
        text: `
		Det er vigtigt, at du er opmærksom på, at alle hold skal sættes efter samme rangliste i en given runde.
		Se reglementet for DH turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>
		Du kan altid skifte rangliste for holdrunden eller sætte en specifik rangliste
		`,
    },
    {
        title: "Hvornår kommer Badmintonligaen og 1. division med?",
        text: `Nembadminton.dk udvikles frivilligt, og det tager tid at udvikle. Derfor kan det heller ikke siges, hvornår liga og 1. division kommer med. Det er muligt at tilføje hold for Badmintonligaen og 1. division, men det er ikke fuldt ud testet færdigt endnu.`,
    },
    {
        title: "Hvilken rangliste skal jeg bruge?",
        text: `Se reglementet for DH-turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>
		Den først offentliggjorte rangliste i en ny måned er gældende for holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende måned.`,
    },
    {
        title: "Spørgsmål til reglementet for DH-turneringen?",
        text: `Kontakt Badminton Danmark. Gå til <a href="https://badminton.dk">https://badminton.dk</a> eller se DH-reglementet <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>`,
    },
    {
        title: "Hvor ofte synkroniserer nembadminton.dk med badmintonplayer.dk?",
        text: `Hver nat opdateres ranglistepoint og nye medlemmer importeres`,
    },
    {
        title: "Hvordan virker validering af holdopstillingen?",
        text: `Valideringen tager udgangspunkt i DH-reglementet (<a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>).<br>Mellem holdene kontrolleres det, om der er spillere på et lavere rangerende hold, der er bedre placeret på NIVEAU-ranglisten (plus-minus buffer).<br>Internt på holdet kontrolleres det, om der er spillere, der står for lavt i en kategori internt på holdet. Det gøres både i single (buffer 50 point) og double (buffer 100 point).`,
    },
    {
        title: "Er der taget højde for U17/U19 spillere?",
        text: `U17/U19 spillere markeres på opstillingen og ranglistepointene for spillerne hentes, men der markeres aldrig for ulovligt hold pga. U17/U19 spillere..`,
    },
    {
        title: "Vi har tabt en protest efter at have stillet med et hold valideret på nembadminton.dk. Hvem betaler boden?",
        text: `Nembadminton.dk er en service og et værktøj lavet og stillet til rådighed af frivillige. Det er derfor altid klubbens og trænerens ansvar at kontrollere, at opstillingerne er korrekte.`,
    }
];

export {faqs}
