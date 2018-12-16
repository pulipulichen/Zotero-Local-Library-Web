#include <File.au3>
#include <FileConstants.au3>
#include <MsgBoxConstants.au3>
#include <WinAPIFiles.au3>

WinClose("[Title:Zotero; Class:MozillaWindowClass]", "")
WinClose("[Title:進階搜尋; Class:MozillaWindowClass]", "")

Local $sFilePath = "..\\config.ini"
Local $sRead = IniRead($sFilePath, "globals", "ZOTERO_DATA_PATH", "Zotero\zotero.sqlite")
;Local $sZoteroSqliteJournal = $sRead & 

Sleep(1000)
Local $aProcessList = ProcessList("zotero.exe")
For $i = 1 To $aProcessList[0][0]
	 ProcessClose($aProcessList[$i][1])
Next