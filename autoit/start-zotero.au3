#include <File.au3>
#include <FileConstants.au3>
#include <MsgBoxConstants.au3>
#include <WinAPIFiles.au3>

Local $CommandLine = $CmdLine

For $i = 1 To $CommandLine[0]
    Local $cmd = $CommandLine[$i]
    ;MsgBox($MB_SYSTEMMODAL, "Config Error", @ComSpec & " /c " & '"' & $cmd & '"')
    ;Run(@ComSpec & " /c " & '"' & $cmd & '"')
    Run($cmd)
Next