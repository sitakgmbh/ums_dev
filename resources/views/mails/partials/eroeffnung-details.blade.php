<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:200px; padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Vorname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->vorname }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Nachname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->nachname }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Arbeitsort:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->arbeitsort?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Unternehmenseinheit:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->unternehmenseinheit?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Abteilung:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->abteilung?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Funktion:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->funktion?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Eintrittsdatum:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->vertragsbeginn->format("d.m.Y") }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Benutzername:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->benutzername }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->email }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Telefon:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->tel_nr }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Bezugsperson:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->bezugsperson?->display_name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Bezugsperson E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->bezugsperson?->email }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->antragsteller?->display_name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->antragsteller?->email }}</td>
    </tr>
</table>
