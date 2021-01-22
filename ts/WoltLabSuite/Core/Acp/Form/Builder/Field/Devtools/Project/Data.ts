export interface PackageData {
  packageIdentifier: string;
}

export interface ExcludedPackageData extends PackageData {
  version: string;
}

export interface RequiredPackageData extends PackageData {
  file: boolean;
  minVersion: string;
}
