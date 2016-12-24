// "all" = anyone can touch, "owner" = only the owner can touch
// "group" = anyone in the same group as the prim
// "list" = only those in the alist below can touch the prim
string allow = "all";
// only set this if above is set to list
// first and last name only please. Ex: "Fumbles McStupid"
list alist = [];
// volume of the sound. 0.0 = silent while 1.0 is max volume
float volume = 0.5;
// this script can handle multiple sound files in the same prim as this script
default {
	touch_end(integer num_detected) {
		if (allow == "all" || allow == "owner" && llDetectedKey(0) == llGetOwner() || allow == "group" && llSameGroup(llDetectedKey(0)) || allow == "list" && llListFindList(alist, [llDetectedName(0)]) != -1) {
			llStopSound();
			integer iCount = llGetInventoryNumber(INVENTORY_SOUND);
			integer inv = llRound(llFrand(iCount));
			if (inv == iCount) {
				inv = 0;
			}
			string sName = llGetInventoryName(INVENTORY_SOUND, inv);
			llPlaySound(sName, volume);
		}
	}
}