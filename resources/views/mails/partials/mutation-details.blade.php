<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:200px; padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Vorname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->vorname }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Nachname:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->nachname }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Arbeitsort:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->arbeitsort?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Unternehmenseinheit:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->unternehmenseinheit?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Abteilung:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->abteilung?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Funktion:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->funktion?->name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Eintrittsdatum:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->vertragsbeginn }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Benutzername:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->benutzername }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->email }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Telefon:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->tel_nr }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->antragsteller?->display_name }}</td>
    </tr>
    <tr>
        <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Antragsteller E-Mail:</td>
        <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $mutation->antragsteller?->mail }}</td>
    </tr>
</table>
