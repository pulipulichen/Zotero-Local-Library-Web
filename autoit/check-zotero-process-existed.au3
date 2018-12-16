Local $aProcessList = ProcessList("zotero.exe")

If $aProcessList[0][0] > 0 Then
   ConsoleWrite("true")
Else
   ConsoleWrite("false")
EndIf
