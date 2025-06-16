import Vue from "vue";

declare module "@vue/runtime-core" {
  export interface ComponentCustomProperties {
    $t: (key: string, ...args: any[]) => string;
    $d: (key: string, ...args: any[]) => string;
    $tm: (key: string, ...args: any[]) => string;
    $rt: (key: string, ...args: any[]) => string;
  }
}
